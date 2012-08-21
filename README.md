MIANAGIOSPLUGIN -- An extensible framework for Nagios plugins written in php5 
=============================================================================

This is a framework for quickly developping and easily maintaining Nagios plugins.
It is written in php 5.

## FEATURES

- plugin help (-h) automatically generated
- debug assistance (-v)
- inheritance : availables classes for
  - SNMP (simple or indexed data)
  - Mysql
  - Oracle 
  - Redis
  - unix file (age)
- filters for altering plugin output at different level.	
- lots of samples
- status and perfdata auto-formatting.

## REQUIREMENTS
- php 5.3+ on monitored hosts
- php modules for certain classes (Oracle, Redis...)
- nrpe on monitored hosts (plus config to execute plugins)

## TODO
- documentation on main methods 
- get rid of french messages !

## Screenshots

![Screenshot](https://github.com/ojdupuis/mianagiosplugin/blog/master/demo.png)


