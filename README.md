# Etherpad Lite Plugin for ILIAS 4.1 - 4.3 #

If you are using ILIAS 4.1 - 4.2 please use version 0.0.4 or the 'pre-43' branch: https://github.com/jrocho/ILIAS-Etherpad-Lite-Plugin/tags
Refer to the README file contained in that version


## How to install: ##

If your are updating from a previous version, please refer to the update section below.

### 1. Install Etherpad Lite 

   Please refer to the Etherpad Lite [installation instructions](https://github.com/ether/etherpad-lite)

*IMPORTANT: Before you start the Etherpad Lite service turn of "minify" in the settings.json otherwise the the JavaScript modifications (see section 2 of this documentation) won't take effect.*
   
   Some recommendations on this: place the Etherpad Lite server behind a reverse proxy, move it from 
   SQLite to MySQL, setup an Init script (to start the Etherpad Lite server automatically), install abiword
   for PDF/Word/OpenOffice import/export. Everything is described in the Etherpad Lite wiki on GitHub.

   This was tested with Debian 5 (using the Debian 6 script) and Debian 6 with nginx and Apache reverse proxy
   setups. 
   
   If you want to only allow access to your pad server to ILIAS user with a session (no direct access to you pad domain)
   set
   
`"requireSession" : true,`
   
in the settings.json of Etherpad Lite
   
Set the IP address in the settings.json to 0.0.0.0
   
e.g.
   
`"ip": "0.0.0.0",`

As of v1.0.0 the Etherpad-Lite Plugin contains support for the EtherpadLite *ep_headings* plugin. To install Etherpad-Lite Plugins in Etherpad-Lite please configure an
admin user in your *settings.json* (in the Etherpad-Lite folder) and then open the URL http://YOUR-PAD-SERVER/admin/plugins
   
### 2. Copy pad.js to Etherpad Lite installation

   Copy the file pad.js.sample to *"static/custom/pad.js"* within your etherpad-lite (server) folder. It add the functionality
   to add/remove individual functions from within ILIAS.

### 3. Copy Plugin to ILIAS

   Copy the plugin files to *Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/*
   in the directory structure of your ILIAS installation

### 4. Enable Plugin in ILIAS 

Login to your ILIAS installation as a administrator and visit Administration -> Modules, Services and Plugins -> Administrate (on the "RepositoryObject" row (the second column of that row should already list the plugin as "EtherpadLite").

Click on "Update" and the on "Activate". The plugin should now be available and you can start to add Etherpads in you courses. You might need to  allow the creation of "Etherpad Lite" objects in your role administration.

### 4. Modify Plugin Configuration

As of v1.0.0 the plugin has to be configured via the ILIAS Administration -> Modules, Services and Plugins -> Administrate (on the "RepositoryObject" row (the second column of that row should already list the plugin as "EtherpadLite") -> Configure

All settings can be configured at that point. It also allows you to configure what buttons users see and if Etherpad object owners can change the settings for individual Etherpads.

## Updating from previous version ##

Replace the files in your ILIAS plugin directory for EtherpadLite (*Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/*).

Open up the Administration Panel and navigate to the repository object plugin administration. Check if the plugin needs to be updated and please also reload the language files for the plugin.

Once updating from a previous version to v1.0.0 or later your configuration from the *etherpadlite.ini.php* will be automatically imported into the database and further configuration is
possible via the ILIAS administration panel.

After updating to a v1.0.0+ version you are free to delete the old *etherpadlite.ini.php*. Please also check the individual configuration options in the ILIAS administration page for this plugin
to see if the configuration is set to your needs.

## Changelog ##

### v1.0.0 ###

A major update for the ILIAS 4.3 version of the plugin. Thanks to contributions by Timon Amstutz from the University of Bern.

* Configuration now directly done in the ILIAS administration (instead of the old etherpadlite.ini.php)
* Support for the ep_headings plugin (for Etherpad-Lite)
* Security improvements (individual Group for each EtherpadLite-Plugin object)
* Enable/disable global EtherpadLite options via the ILIAS administration or allow EtherpadLite owners to configure pads individually
* Fullscreen mode


### v0.0.7 ###

* database Bugfix (introduced in v0.0.6)

### v0.0.6 ###

Only available for ILIAS 4.3.

* removed the need to edit the etherpad.js file
* Added preferences to disable/enable user colors, chat, line numbers and the control buttons for individual Etherpads

### v0.0.5 ###

* same as v0.0.4 but modified for ILIAS 4.3 

### v0.0.4 ###

* Updated copyright notice, removed example code, general code clean-up (latest version for ILIAS 4.2)

## Contact/Responsible ##

Jan Rocho <jan.rocho@fh-dortmund.de>

Contributions by: Timon Amstutz <timon.amstutz@ilub.unibe.ch>

---

### This plugin uses/includes ###

Etherpad Lite PHP Client library (modified) from: 
https://github.com/TomNomNom/etherpad-lite-client

   Modified for HTTPS support

