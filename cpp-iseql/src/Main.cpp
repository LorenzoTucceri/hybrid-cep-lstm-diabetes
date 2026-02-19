#include <iostream>
#include <fstream>
#include <sstream>
#include <vector>
#include <string>
#include <unordered_set>
#include "util/Arguments.h"
#include "algorithms/Joins.h"
#include "MainBefore.h"
#include "MainLatency.h"
#include "MainJoins.h"
#include <chrono>


// Struttura per rappresentare gli eventi
struct Event
{
    long start_time; // Timestamp Unix di inizio
    long end_time; // Timestamp Unix di fine
    std::string event_type; // Tipo di evento

    // Operatore di stampa per un evento
    friend std::ostream& operator<<(std::ostream& os, const Event& e)
    {
        os << e.start_time << " -- " << e.end_time << " -- " << e.event_type;
        return os;
    }
};

std::string trim(const std::string& s)
{
    size_t start = s.find_first_not_of(" \t\r\n");
    size_t end = s.find_last_not_of(" \t\r\n");

    return (start == std::string::npos) ? "" : s.substr(start, end - start + 1);
}

// Funzione per leggere gli eventi dal file
std::vector<Event> readEventsFromFile(const std::string& filename)
{
    std::vector<Event> events;
    std::ifstream file(filename);

    if (!file)
    {
        std::cerr << "Errore nell'aprire il file " << filename << std::endl;
        return events;
    }

    std::string line;
    std::getline(file, line); // Salta l'intestazione

    while (std::getline(file, line))
    {
        std::stringstream ss(line);
        std::string start_time_str, end_time_str, event_type;

        std::getline(ss, start_time_str, ',');
        std::getline(ss, end_time_str, ',');
        std::getline(ss, event_type, ',');
        event_type = trim(event_type);
        // Converti i timestamp da stringa a long
        long start_time = std::stol(start_time_str);
        long end_time = std::stol(end_time_str);

        // Verifica che il timestamp di inizio sia minore del timestamp di fine
        if (start_time >= end_time)
        {
            // std::cerr << "Errore: start_time >= end_time per l'evento " << event_type
            //       << " (" << start_time << " >= " << end_time << "). Ignorando questo evento.\n";
            continue; // Salta questo evento
        }

        events.push_back(Event{start_time, end_time, event_type});
    }

    return events;
}

struct pair_hash
{
    template <typename T1, typename T2>
    std::size_t operator ()(const std::pair<T1, T2>& p) const
    {
        auto h1 = std::hash<T1>{}(p.first);
        auto h2 = std::hash<T2>{}(p.second);
        return h1 ^ h2; // XOR tra gli hash
    }
};

static std::string toLower(const std::string& s)
{
    std::string out = s;
    std::transform(out.begin(), out.end(), out.begin(),
                   [](unsigned char c) { return std::tolower(c); });
    return out;
}

std::vector<std::string> parsePatternLine(const std::string& line)
{
    std::vector<std::string> tokens;
    std::stringstream ss(line);
    std::string item;

    while (std::getline(ss, item, ','))
    {
        item.erase(0, item.find_first_not_of(" \t\r\n"));
        item.erase(item.find_last_not_of(" \t\r\n") + 1);
        if (!item.empty())
            tokens.push_back(item);
    }
    return tokens;
}

bool tokenMatchesEventType(const std::string& token, const std::string& event_type)
{
    std::string t = toLower(token);
    std::string e = toLower(event_type);

    if (t == e) return true;
    if (e.find(t) != std::string::npos) return true;
    if (t.find(e) != std::string::npos) return true;

    return false;
}

int main(int /*argc*/, const char* argv[])
{
    std::cout << "ISEQL    ";
    std::cout << sizeof(size_t) * 8 << "-bit    ";
    std::cout << "Compiled on " __DATE__ " " __TIME__ "    ";
    std::cout << "Tuple size " << sizeof(Tuple) << " bytes";
#ifdef NDEBUG
    std::cout << "    Release";
#else
    std::cout << "    Debug";
#endif
#ifdef COUNTERS
    std::cout << "    COUNTERS";
#endif
    std::cout << std::endl;

    Arguments arguments{argv};
    std::string command = arguments.getCurrentArgAndSkipIt("Command");

    if (command == "before")
    {
        mainBefore(arguments);
    }
    else if (command == "latency")
    {
        mainLatency(arguments);
    }
    else if (command == "time-swing")
    {
        // Leggi gli eventi dal file "eventi.txt"
        //
        std::vector<Event> events = readEventsFromFile("/Users/lorenzotucceri/Progetti/ISEQL/backend-iseql/eventi.txt");
        //std::vector<Event> events = readEventsFromFile("../../backend-iseql/eventi.txt");

        if (events.empty())
        {
            std::cerr << "Errore: nessun evento letto dal file." << std::endl;
            return 1; // Uscita con errore se non ci sono eventi
        }
        // Assicurati che ci siano almeno 2 eventi per fare un join
        if (events.size() < 2)
        {
            std::cerr << "Non ci sono abbastanza eventi per fare un join." << std::endl;
            return 1;
        }

        Relation R, S;

        int id_counter = 0;
        for (const auto& e : events)
        {
            if (e.start_time >= e.end_time)
            {
                //std::cerr << id_counter << " Evento ignorato: start_time >= end_time (" << e.start_time << " >= " << e.
                //  end_time << ")\n";

                continue;
            }

            if (e.event_type == "normal")
                continue;

            auto start = static_cast<Timestamp>(e.start_time);
            auto end = static_cast<Timestamp>(e.end_time);

            //std::cout << id_counter << " Tuple: start=" << start << ", end=" << end << ", type=" << e.event_type <<
            //      std::endl;

            R.push_back({start, end, id_counter, e.event_type});
            S.push_back({start, end, id_counter, e.event_type});

            id_counter++;
        }

        Index indexR;
        indexR.buildFor(R);
        Index indexS;
        indexS.buildFor(S);

        R.setIndex(indexR);
        S.setIndex(indexS);

        beforeJoin(R, S, 7200, [](const Tuple& r, const Tuple& s)
        {
            if (r.id == s.id) return;

            if (r.event_type == s.event_type) return;

            static const std::unordered_set<std::pair<std::string, std::string>, pair_hash> skip_pairs = {
                {"extremely_high", "high"},
                {"low", "extremely_low"}
            };

            // Verifica se la coppia esiste, considerando l'ordine
            if (skip_pairs.count({r.event_type, s.event_type}) || skip_pairs.count({s.event_type, r.event_type}))
                return;

            std::cout << r << " -- " << s << std::endl;
        });
    }
    else if (command == "extremely-time-swing")
    {
        // Leggi gli eventi dal file "eventi.txt"
        //
        std::vector<Event> events = readEventsFromFile("/Users/lorenzotucceri/Progetti/ISEQL/backend-iseql/eventi.txt");
        //std::vector<Event> events = readEventsFromFile("../../backend-iseql/eventi.txt");

        if (events.empty())
        {
            std::cerr << "Errore: nessun evento letto dal file." << std::endl;
            return 1; // Uscita con errore se non ci sono eventi
        }
        // Assicurati che ci siano almeno 2 eventi per fare un join
        if (events.size() < 2)
        {
            std::cerr << "Non ci sono abbastanza eventi per fare un join." << std::endl;
            return 1;
        }

        Relation R, S;

        int id_counter = 0;
        for (const auto& e : events)
        {
            if (e.start_time >= e.end_time)
            {
                //std::cerr << id_counter << " Evento ignorato: start_time >= end_time (" << e.start_time << " >= " << e.
                //  end_time << ")\n";

                continue;
            }

            if (e.event_type == "normal")
                continue;

            auto start = static_cast<Timestamp>(e.start_time);
            auto end = static_cast<Timestamp>(e.end_time);

            //std::cout << id_counter << " Tuple: start=" << start << ", end=" << end << ", type=" << e.event_type <<
            //      std::endl;

            R.push_back({start, end, id_counter, e.event_type});
            S.push_back({start, end, id_counter, e.event_type});

            id_counter++;
        }

        Index indexR;
        indexR.buildFor(R);
        Index indexS;
        indexS.buildFor(S);

        R.setIndex(indexR);
        S.setIndex(indexS);
        auto start_total = std::chrono::high_resolution_clock::now();

        // SOGLIA DI 30 MINUTI (PER ORA)

        beforeJoin(R, S, 1800, [](const Tuple& r, const Tuple& s)
        {
            if (r.id == s.id) return; // Evita confronto con sé stessa

            if ((r.event_type == "extremely_high" && s.event_type == "extremely_high") || (r.event_type ==
                "extremely_low" && s.event_type == "extremely_low"))
                std::cout << r << " -- " << s << std::endl;
        });
        auto end_total = std::chrono::high_resolution_clock::now();
        auto duration_ms = std::chrono::duration_cast<std::chrono::milliseconds>(end_total - start_total).count();

        std::cout << "\nTempo esecuzione beforeJoin: " << duration_ms << " ms" << std::endl;
    }
    else if (command == "detection-pattern")
    {
        std::string pattern_file = arguments.getCurrentArgAndSkipIt("PatternFile");
        std::ifstream file(pattern_file);
        if (!file)
        {
            std::cerr << "[DEBUG] Errore nell'aprire il file: " << pattern_file << std::endl;
            return 1;
        }

        // Leggi tutti gli eventi dal CSV
        std::vector<Event> raw_events = readEventsFromFile("/Users/lorenzotucceri/Progetti/ISEQL/backend-iseql/eventi.txt");
        if (raw_events.empty())
        {
            std::cerr << "Errore: nessun evento letto dal file." << std::endl;
            return 1;
        }

        // Mantieni tutti gli eventi validi e salva start/end
        std::vector<std::pair<long, long>> start_end_pairs;
        std::vector<std::string> event_types;
        for (const auto& e : raw_events)
        {
            if (e.start_time < e.end_time)
            {
                start_end_pairs.emplace_back(e.start_time, e.end_time);
                event_types.push_back(e.event_type);
            }
        }

        struct PatternResult
        {
            std::string pattern;
            int frequency;
            std::vector<std::vector<std::pair<long,long>>> occurrences;
        };

        std::vector<PatternResult> results;

        std::string line;
        while (std::getline(file, line))
        {
            line = trim(line);
            if (line.empty()) continue;

            std::vector<std::string> pattern = parsePatternLine(line);
            if (pattern.size() < 2) continue;

            PatternResult result;
            result.pattern = line;
            result.frequency = 0;

            // Ciclo su tutte le possibili posizioni di partenza
            for (size_t start_idx = 0; start_idx + pattern.size() <= event_types.size(); ++start_idx)
            {
                bool full_match = true;
                for (size_t k = 0; k < pattern.size(); ++k)
                {
                    if (!tokenMatchesEventType(pattern[k], event_types[start_idx + k]))
                    {
                        full_match = false;
                        break;
                    }
                }

                if (!full_match) continue;

                // Verifica se il match totale rientra nelle 24 ore
                long start_first = start_end_pairs[start_idx].first;
                long end_last = start_end_pairs[start_idx + pattern.size() - 1].second;
                if ((end_last - start_first) > 24 * 3600) continue; // oltre 24 ore → skip

                // Match valido: salva frequenza e occorrenze
                result.frequency++;
                std::vector<std::pair<long,long>> match_intervals;
                for (size_t k = 0; k < pattern.size(); ++k)
                    match_intervals.push_back(start_end_pairs[start_idx + k]);

                result.occurrences.push_back(match_intervals);
            }

            results.push_back(result);
        }

        // Stampa dei risultati
        std::cout << "\n[DEBUG] Risultati finali:\n";
        for (const auto& r : results)
        {
            std::cout << "{ pattern: '" << r.pattern
                      << "', frequency: " << r.frequency
                      << ", occurrences: [";
            for (size_t i = 0; i < r.occurrences.size(); ++i)
            {
                std::cout << "[";
                for (size_t j = 0; j < r.occurrences[i].size(); ++j)
                {
                    std::cout << "(" << r.occurrences[i][j].first << "," << r.occurrences[i][j].second << ")";
                    if (j + 1 < r.occurrences[i].size()) std::cout << ",";
                }
                std::cout << "]";
                if (i + 1 < r.occurrences.size()) std::cout << ",";
            }
            std::cout << "] }\n";
        }

        std::cout << "\n[DEBUG] Fine lettura e ricerca pattern" << std::endl;
    }


    else
    {
        mainJoins(command, arguments);
    }

    return 0;
}
