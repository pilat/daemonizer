# PHP Daemonizer #

Provide simple way for creating long-running php daemons.

[![Build Status](https://travis-ci.org/pilat/daemonizer.svg)](https://travis-ci.org/pilat/daemonizer)

#### How it use:
* Your class must implement "DaemonizerInterface" interface.
* Create cli-daemonizer.php file in config or root directory
* Start daemon, use command ./bin/daemonizer start

cli-daemonizer.php sample file contains:

```
<?php

return array(
    new CronJobExample(),
    new TickExample()
);
```
