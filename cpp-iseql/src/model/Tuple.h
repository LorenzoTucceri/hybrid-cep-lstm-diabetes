#pragma once

#include "model/Interval.h"
#include <string>
#include <iostream>

class Tuple : public Interval
{
public:
	int id;
	std::string event_type;

public:
	Tuple() noexcept {}

	Tuple(Timestamp start, Timestamp end, int id = -1, const std::string& event = "") noexcept
		: Interval(start, end), id(id), event_type(event)
	{
	}

	friend std::ostream& operator << (std::ostream &out, const Tuple& tuple)
	{
		return out << static_cast<const Interval&>(tuple) << ' ' << tuple.id << ' ' << tuple.event_type;
	}

	int getId() const noexcept
	{
		return id;
	}

	const std::string& getTypeEvent() const noexcept
	{
		return event_type;
	}
};