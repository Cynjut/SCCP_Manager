SCCP-Manager v. 1.1 for FreePBX


Introduction.

	This module has been developed to help IT Staff with their Asterisk-Cisco infrastructure deployment, 
providing easily provisioning and managing Cisco IP phones and extensions in a similar way as it does with Cisco CallManager.

Advantages.

As we are using SCCP Channel, no SIP firwmare upgrade is needed for each phone, saving a lot of time 
and money (you can not come back from SIP to SCCP under CallManager without paying new licenses).  

If you are thinking to migrate from CallManager to Asterisk (or did it), SCCP-Manager allows you 
to administer SCCP extensions and a wide range of Cisco phone types (including IP Communicator).
You can control phone buttons (depending on the phone model) assigning multiple lines, speeddials and BLF’s.
And you can also reset phones from the module GUI.


Requirements.

- Chan-SCCP V3.1.0 (or later) channel driver for Asterisk (http://chan-sccp-b.sourceforge.net/)
- TFTP Server running under /tftpboot/


Module installation:

1. Download module into your local system.
2. Goto FreePBX Admin -> Module Admin.
3. Click Upload Modules.
4. Browse to the location of the module on your computer and select Upload.
5. Click Manage Local Modules.
6. Find and click SCCP Manager. Check Install. Click Process button.
7. Confirm installation.
8. Close Status window.
9. Apply Config to FreePBX.
10. Six new forms are available in Applications:
    -   SCCP TFTP.
    -   SCCP Config.
    -   SCCP Devmodel.
    -   SCCP Phones.
    -   SCCP Extension.
    -   SCCP Keysets.

IMPORTANT NOTE: 
This system assumes you are using the SCCP Real-time Database. If you are
not yet using the RTD, you will need to set it up for this module to be
effective.

SCCP TFTP:

This module manages the 'standard' files in your TFTP directory. In
this version of the system, it *MUST* be called "/tftpboot" and be 
in the root directory of the machine with the webserver on it.

The program assumes that you will be managing all of your phones
using the TFTP files, so it creates the appropriate files and 
sets the defaults to whatever you have defined in the 
XMLDefault.cnf.xml file. Since the settings in this file are used
when the phone can't find a machine specific config file, you
don't technically need per-machine files if all of your settings
are correct in the XMLDefault file.

SCCP Config:

This module allows you to set up the basic configuration of your sccp.conf
file. This file will be created in your $ASTETCDIR directory if it 
doesn't already exist. If the file does exist, the current settings will
be read from the file and used. Whenever possible, we provide a list
of valid choices for fields that have a limited set of options.

SCCP Devmodel:

This module manages the application specific file that contains a lot
of information about the models and options of the different SCCP devices
available in the system. A lot of models are included - feel free to
remove any you don't need. 

SCCP Phones:

This module allows you to connect your phones and lines together. 
Note that this module really ties all the rest of the modules
together. If provides an interface to allow you to set the 
softkeyset, the different line buttons, and other features of the
actual phones.

SCCP Extension.

This module connects the extension information in the SCCP system.
If you specify a non-existent extension, a skeleton definition 
will be created and you will have the opportunity to edit that
definition. 

SCCP Keysets:

This module allows you to review the default softkeyset (which
is always readonly) and to create other keysets that you can 
use in your phone definitions.


