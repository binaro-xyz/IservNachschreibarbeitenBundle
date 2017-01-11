Hint: All these commands must be run as root

# Install

## Install via our repo
1. Import the repo’s GPG key by running either
    * `apt-key adv --keyserver keyserver.ubuntu.com --recv EF3D0E88` or
    * `wget -O - https://benjamin-altpeter.de/EF3D0E88.asc | apt-key add -`
2. Add the repo to your sources by running `echo "deb https://apt.altpeter.me jessie base" | tee /etc/apt/sources.list.d/binaro.list`
3. Run `apt-get update`
4. Install the package via `apt-get install nachschreibarbeiten` and run `iservchk` afterwards. Alternatively, go into the IServ admin section and install the package from there.
5. Clear the cache manually using `php /usr/share/iserv/app/console cache:clear --env=prod; chmod -R 774 /var/cache/iserv/web/prod; service apache2 restart` or wait for IServ to do that automatically.

## Manual install
To install, a priviledge file has to be created: `/usr/share/iserv/priv/` containing the following:
```
mod_nachschreibarbeiten_access:
module          Nachschreibarbeiten
description     Access the Nachschreibarbeiten module
assign          admins

mod_nachschreibarbeiten_admin:
module          Nachschreibarbeiten
description     Administer the Nachschreibarbeiten module
assign          admins
```

Also, this has to be added to the old version's `/usr/share/iserv/db/mod_iserv-nachschreibarbeiten.sql`:
```
-- Iserv 3
GRANT ALL ON mod_nachschreibarbeiten_dates, mod_nachschreibarbeiten_entries TO symfony;
GRANT USAGE, SELECT ON SEQUENCE mod_nachschreibarbeiten_dates_id_seq, mod_nachschreibarbeiten_entries_id_seq TO symfony;
```

Additionally, another file has to be created `/usr/share/iserv/iservcfg/config/30mod_iserv-nachschreibarbeiten`:
```
NachschreibarbeitenInfotext:
name        Infotext im Nachschreibarbeitenmodul
description Im Nachschreibarbeitenmodul kann ein Infotext angezeigt werden, welcher hier eingestellt werden kann.
group       Module: Nachschreibarbeiten
type        string
default         "Die Aufgaben für die Nachschreiber_innen werden im Fach „Nachschreibarbeiten“, das sich unter den Lehrer_innenpostfächern im Heidberg befindet, gesammelt. Zusätzliche Materialien (z.B. Duden, Atlas, Taschenrechner etc.) müssen von den betroffenen Fachlehrkräften organisiert und im entsprechenden Raum (i.d.R. Raum 151, Ausnahmen s. Liste unten) bereitgestellt werden. Einträge in die Liste der Nachschreiber_innen erfolgen bitte bis spätestens zum Vortag (16:00 Uhr) des jeweiligen Nachschreibtermins."
```

Afterwards, clear the cache, run iservchk and restart apache if something doesn't work.

# Uninstall

## Uninstall via apt

Run `apt-get remove nachschreibarbeiten`

## Manual uninstall

1. `rm -rf /usr/share/iserv/src/binaro`
2. `rm /usr/share/iserv/db/mod_iserv-nachschreibarbeiten.sql /usr/share/iserv/iservcfg/config/30mod_nachschreibarbeiten /usr/share/iserv/priv/mod_nachschreibarbeiten`
3. `php /usr/share/iserv/app/console cache:clear --env=prod; chmod -R 774 /var/cache/iserv/web/prod; service apache2 restart`
4. `iservchk`
