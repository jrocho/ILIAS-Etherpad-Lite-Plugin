# Etherpad Lite Plugin for ILIAS #

If you are using ILIAS 4.1 - 4.2 please use version 0.0.4 or the 'pre-43' branch: https://github.com/jrocho/ILIAS-Etherpad-Lite-Plugin/tags
Refer to the README file contained in that version


## How to install: ##

If your are updating from a previous version, please refer to the update section below.

### 1. Install Etherpad Lite 

   Please refer to the Etherpad Lite [installation instructions](https://github.com/ether/etherpad-lite)

*IMPORTANT: Before you start the Etherpad Lite service, turn off "minify" in the settings.json otherwise the JavaScript modifications (see section 2 of this documentation) won't take effect.*

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

As of v1.0.0 the Etherpad-Lite Plugin contains support for the EtherpadLite *ep_headings* plugin. To install Etherpad-Lite Plugins in Etherpad-Lite please configure an admin user in your *settings.json* (in the Etherpad-Lite folder) and then open the URL http://YOUR-PAD-SERVER/admin/plugins

### 2. Copy pad.js / pad.css to Etherpad Lite installation

#### Etherpad < v1.7.5

Copy the file pad.js.sample to *"src/static/custom/pad.js"* and pad.css.sample to *"src/static/custom/pad.css"* within your etherpad-lite (server) folder. It adds the functionality to add/remove individual functions from within ILIAS.

#### Etherpad >= v1.7.5

Copy the file pad.js.sample to *"src/static/skins/no-skin/pad.js"* and pad.css.sample to *"src/static/skins/no-skin/pad.css"* within your etherpad-lite (server) folder. It adds the functionality to add/remove individual functions from within ILIAS.

**Please note:** You have to add the 

```
"skinName": "no-skin",
```

to your Etherpad settings.js if is not already there and change it from the standard "colibris" to "no-skin".

### 3. Copy Plugin to ILIAS

   Copy the plugin files to *Customizing/global/plugins/Services/Repository/RepositoryObject/EtherpadLite/*
   in the directory structure of your ILIAS installation

### 4. Enable Plugin in ILIAS 

Login to your ILIAS installation as an administrator and visit Administration -> Modules, Services and Plugins -> Administrate (on the "RepositoryObject" row [the second column of that row should already list the plugin as "EtherpadLite"]).

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

Please be sure to update the settings.json of Etherpad-Lite and add

<pre><code>
  // Session Key, used for reconnecting user sessions
  // Set this to a secure string at least 10 characters long.  Do not share this value.
  "sessionKey" : "",
</code></pre>

if you are updating from a EtherpadLite version pre v1.2.7. You—of course—need to add a random string.

### Updating to Etherpad Lite Server to >v1.4 and <v1.7.5 ###

Please replace your <EtherpadLiteServer>/src/static/custom/pad.js with the one provided in pad.js.sample. Otherwise the hiding/showing of buttons in the toolbar will not work anymore.

As of Plugin v1.1.1 there is a new setting in the administration which sets the version of the Etherpad-Lite Server. Please choose the right version.

As of v1.0.1 of this ILIAS plugin it is recommended to use Etherpad-Lite higher than v1.2.7

## Changelog ##

### v1.4.4

*supports ILIAS 5.3 - 5.4*

- Added object copy support - copy is a new empty EtherPad (provided by Fred Neumann, FAU)

### v1.4.3 ###

* updated for ILIAS 5.4 (this version supports ILIAS 5.3 - 5.4)

### v1.4.2 ###
* fix for small display sizes
* improved iPad support (please see step 2 in the instructions - you need to replace your old pad.js file and add the new pad.css to your Etherpad Lite server folder for the iPad support to work)

### v1.4.1 ###
* secure session cookie support (Databay)

### v1.4.0 ###
* compatibility with ILIAS 5.3 (use v1.3.1 for older versions)

### v1.3.1 ###
* fixed errors in README [Rillke]
* fixed typo in language file
* switch from boolean to int in database (only affects new installations) [theodortruffer]
* added option to globally disable the export/import button on read-only Etherpads due to a problem how Etherpad Lite handles session based pads

### v1.3.0 ###
* compatibility with ILIAS 5.2

### v1.2.1 ###
* 'Uninstall' now removes the EtherpadLite tables

### v1.2.0 ###
* 5.1 compatibility 

### v1.1.2 ###
* further CSS fix for 5.0
* SVG Icons for ILIAS 5.0
* fix for Mantis report http://ilias.de/mantis/view.php?id=14209

### v1.1.1 ###
* New Version of pad.js.sample (compatible with Etherpad-Lite Server >= v1.4)
* New Setting in plugins administration for the Etherpad-Lite server version (please set it accordingly)
* CSS fix for ILIAS 5.0

### v1.1.0 ###
* Write protection for Etherpads. There is a new setting in the administration which gives administrators of Etherpads the possibility to enable write protection for in individual Etherpads. This feature is turned on by default. Feature thanks to Eric Laubmeyer, Hochschule RheinMain.
* Updated for ILIAS 5.0


### v1.0.6 ###
* added option to disable HTTP certificate verification

### v1.0.5 ###

* added path as a configuration option, if the EtherpadLite is in a sub-directory of the server.

### v1.0.4 ###

* fixed bug when deleting pads from trash

### v1.0.3 ###

* Updated for ILIAS 4.4

### v1.0.2 ###

* removed old .svn directory from templates/default
* hide EtherpadLite object from course members when it is set to offline
* setting default text for EtherpadLite fixed 

### v1.0.1 ###

* patches the API library for EtherpadLite v1.2.7
* fixed database bug when creating Etherpads with the chat function disabled
* added language variables

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

