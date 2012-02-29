Housey - Split Testing Library
=============================

Introduction
------------

This library started as a port of
[A/Bingo](http://www.bingocardcreator.com/abingo/), which is targetted at Ruby
on Rails to straight up PHP.  Like my other Open Source efforts, I was hoping to
create a library that was framework agnostic, so it it's ended up a little messy
and does require a little bit of work to get going.

Storage
-------

Housey uses two kinds of storage. The first we'll refer to as the persistent
storage, the second the cache. Given the way the cache is used, you may actually
want to use a persistant storage engine (such as memcachedb), as described on
the [A/Bingo website](http://www.bingocardcreator.com/abingo/installation).

Usage
-----

See the behat files until I get some documentation, everything under `features/`

Copyright
---------

Copyright (c) 2012 Dave Marshall. See LICENCE for further details
