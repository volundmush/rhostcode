# Volund's RhostCode Suite

## CONTACT INFO
**Name:** Volund

**Email:** volundmush@gmail.com

**PayPal:** volundmush@gmail.com

**Patreon:** https://www.patreon.com/volund

**Discord:** VolundMush

## LICENSE
MIT License. In short, go nuts, but give credit where credit is due. See the included LICENSE file for more details.

## REQUIREMENTS
* A reliable server host.
* Decent Linux shell administration skills.
* The latest version of RhostMUSH. You can find RhostMUSH at https://github.com/RhostMUSH/trunk
* Specific netrhost.conf settings. Those will be detailed below.
* Some optional packages may require a MySQL or MariaDB database that can instantly respond to the game's queries. (Usually means running on same host.)
* A willingness to deal with the weirdness that is MUSHcode.  

## CORE FEATURES
* **MODULAR DESIGN:** Install only the features you want. The CORE INSTALL is designed to get along with most existing code for well-established games.

* **CONFIGURABILITY:** There is a +globalconfig for handling global settings, and many systems have their own /config and /options switches.

* **ACCOUNT-BASED CHARACTER/ALTS MANAGEMENT:** Players can have multiple characters under one account, with shared account settings and data.

* **GLOBAL PARENTS:** A base implementation of global parents with nice formatters.

* **CLEAN CODE:** Although this is somewhat subjective, I've tried to keep my code as clean, readable, efficient, as possible, and leaving many paths open for customization, modding, and expansion.

* **REGION SYSTEM:** The Region system allows organizing THING ZONEMASTERS as collab building security boundaries. Region Objects can form a tree layout for building hierarchial game maps.

* **HELP SYSTEM:** The provided help system supports display categories and subfiles, as well as text-based searching. Everything is elaborately documented in a consistent, easy-to-read style. +help for players, +shelp for admin!

## OPTIONAL FEATURES
* **BULLETIN BOARD:** The BBS has roughly identical syntax and feature-parity with the ever-popular Myrddin's BBS. It optionally integrates with the Faction system to provide Faction boards in different namespaces.

* **CHANNELS:** PennMUSH-styled chat channels with MUX-style alias compatability and fine-grained permissions/locks support. Supports duration-based bans.

* **RADIO SYSTEM:** An offshoot of Channels which allows ordinary players to create and manage their own channels in a separate namespace from the public Channel System.

* **CONVENIENCE:** A plethora of player globals and conveniences, such as multi-descer, +beep, +dice, +flip, and +who.

* **THEME:** An +fclist/+theme system is provided for multi-theme/crossover games that need to track feature/played characters. Themes can also have notes/text files attached for tracking important information.

* **FACTION SYSTEM:** A Faction system handles representing all groups, organizations, guilds, and similar associations, with a flexible rank structure, and configurable permissions and membership visibility. Includes global eval locks for locking resources to factions in various ways.

* **INFO SYSTEM:** The +info system allows 'text notes' to be created on players and optionally locked by admin, visible to all other players. Many sub-varieties can be easily created with different permissions, visibility, and namespaces.

* **TICKET TRACKER:** Inspired by the ever-popular Anomaly Jobs, the Ticket Tracker handles trouble tickets/requests/issues across various Buckets, with flexible levels of access and administration.

* **SCENE SYSTEM / ROLEPLAY LOGGING:** The Scene Management System is a very advanced scene scheduling and automatic logging system that uses SQL. Also included is a Pose Tracker (+pot) for local storage and convenience in roleplay.

## PLANNED FEATURES
These aren't in yet, but will be soon.

* **FINGER:** An incredibly powerful +finger and +oocfinger that can be configured to be whatever a game owner wants it to be.

* **FRIEND / WATCHFOR:** A Friends/watch list lets players keep track of their friends and alert them when they connect or disconnect.

* **WHO AND WHERE:** A Who and Where command is provided that integrates with many other systems. Don't like how Who looks? Change it with DynamicData strings!

* **LOGIN TRACKER:** If installed, the Login Tracker records IP addresses and times of every login attempt, helping players and admin keep an eye out for suspicious activity.

* **IDLE SWEEPER:**: Give it a destination, a run interval, and an idle limit, and this will keep the grid clear of idlers.

* **POLL SYSTEM:** Hold global polls that can accept given options and allow for 'Other' field answers.

* **GUEST MANAGEMENT:** Creating and keeping track of Guests is easy and fully explained. This code makes sure that Guests return to a specific room after disconnect, will have access to a guest channel, and allows them to adopt a temporary custom name.

## CONSIDERATIONS AND NOTES
1. Although this code was designed with the hope that it will be easy to install on an established game, those are always case-by-case situations.
2. This code was primarily written to enable games that operate as themed, fairly open roleplay environments. It is not designed for games that focus on a great deal of privacy or secrecy between players, like hiding names.
3. This code uses action lists for control flow wherever possible. Although that's technically idiomatic to MUSHcode, it goes against the grain of much traditional MUSHcode. Interoperability with legacy stuff from sites like mushcode.com is not guaranteed.

## INSTALLATION GUIDE
This readme assumes you at least know the basics of what you're doing by installing softcode and running a game, have a host, know how to get the game running and back it up, edit its files, etc. If you don't, I'd suggest checking out these fine people over at the Rhost Dev server, rhostdev.mushpark.com 4201, or their [Discord](https://discord.gg/RxxtfVRYA4) - most coders there could easily get you going in a flash.

You should refer to the [official Rhost installation guide](https://github.com/RhostMUSH/trunk/wiki/Installation) while following these instructions. I can't write instructions that cover every key-press and command.

These instructions written on 7/9/2024 for Rhost 4.4.2. Rhost's default settings may change in the future, so these instructions and provided settings may not be accurate for future versions.

### Obtain and configure RhostMUSH
`git clone https://github.com/RhostMUSH/trunk rhost`

`cd rhost/Server`

`make confsource`

#### make confsource options:
* 29: Recommend a minimum of 30 extra registers. Just in case.
* 7: Disable hardcoded +help
* 28: Enable the Lua API.
* B1: If you're planning on using the roleplay logger or other amenities that need MySQL, enable it.
* 27: Enable Websockets.
* B6: LBUF size. Option 4 (32K) is recommended. our codesuite.conf already includes the recommended output_limit
* B4: You don't have to enable SQLite support, but it can be handy to have.

#### Compile!
`[r]un` the compile and use `make links` when prompted.

Then `cd game`

#### netrhost.conf options:
Go to the bottom and add...
`include codesuite.conf`

#### extra .conf files
Put `codesuite.conf`, `objects.conf`, and any other `*.conf` files from subsystems like storyteller in the `game` directory. You can do this in the shell with a text editor like `nano` or `vim` if you know how to use them, or transfer them via tools like FileZilla or WinSCP.

Highly recommend these files be reviewed, to learn what they do.

#### Handle ulimit
_DO NOT SKIP THIS STEP!_

It's very important that RhostMUSH be given a sufficient stack size, or it will crash without warning under heavy load and likely corrupt your database. With 32kb LBUFs and more The RhostMUSH server requires an increased stack size over the defaults.

On Linux (I did my testing on Debian/Ubuntu) this is handled by the `ulimit` command. 

The easiest way to ensure it always has sufficient stack size is to add a command to the `Startmush` file in `rhost/Server/game`.

For example, it could look like:

```
#!/bin/sh
#
#       Startmush - Kick off the netmush process.
#
ulimit -s 128000
lc_chk=`echo "$1"|tr '[:upper:]' '[:lower:]'`
```

#### Start 'er up!
`./Startmush` to get the game going.

### Installing and Configuring the Codesuite:
1. Login to the #1 (God) character via `_conn #1 Nyctasia`. you gotta use `_conn` because we have a softcoded connect command!
2. `@pcreate CodeHolder=<password>` to create a new character to hold the code. `@set *CodeHolder=IMMORTAL INHERIT` to give them full privileges.
3. For the love of Pete, change the #1 password. `@password Nyctasia=<newpassword>`
4. Log out of #1 and log in as CodeHolder.
5. `@dig Master Room` to create a Master Room/Global Room. Note its dbref.
6. Edit `objects.conf` to set the `master_room` parameter to this dbref. strip the #. it should be something like `master_room 3`
7. Type `@reboot` and use `think globalroom()` to confirm that it's working.
8. Copy and paste the contents of the CORE 01, CORE 02, and CORE 03 files into the game.
9. Copy and paste the contents of `rhost/Mushcode/softfunctions.minmax` into the game then `@tel Softfunctions=#stor` to put it in the storage object. Then, paste in the contents of `rhost/Mushcode/scan` and put the resulting object in the master room (#3).
10. `@reboot` again. Confirm that `@function` has the new global functions listed.
11. Copy and paste the contents of the remaining CORE files in numeric order to install everything else.
12. edit `objects.conf` again to set the other Utility Object and Global Parent dbrefs just like you did the master room. you can use `@tag/list 0` to review everything that was created.
13. `@reboot` again.

At this point, the CORE should be installed. You'll notice that the login screen now uses new commands. By default, only bittype 2+ and guests can login using the `connect` command. Everyone else needs to create accounts and use the `login` command.

It would be wise to create your actual admin character using the new account system, give them whatever permissions you wish, and only login CodeHolder for debugging and maintenance.

13. Use `+globalconfig` to configure all relevant settings as appropriate to your game.

14. Have fun!

#### Additional Systems
There are plenty more things you can use besides just the CORE. Other systems, like the Theme System or Bulletin Board system, may have dependencies. The files always contain such notes at the beginning as comments.

Keep in mind that pasting in large files drains a lot of `@cmdquota`. Use `@cmdquota me=99999` to give yourself plenty. That means you can enter 99999 lines of code at once before being throttled.

## FAQ
**Q:** Why did you write this?

**A:** Mostly because I love roleplaying on MUSHes and want to make it as easy as possible to open and run them.

**Q:** What about PennMUSH, TinyMUX, etc?

**A:** If you want PennMUSH, then go check out the [predecessor of this codesuite](https://github.com/volundmush/mushcode). It supports PennMUSH and RhostMUSH. However, I decided that 4.0 will be Rhost-only in order to focus on all of Rhost's features and advantages. Offering cross-compatability means a massive compromise and being limited to the lowest common denominator.

**Q:** Can I help?

**A:** Absolutely! I'm always open to suggestions, bug reports, and pull requests.

**Q:** Why is this so complicated?

**A:** MUSHcode is a weird beast. It's a language that's been around for decades and has a lot of quirks and oddities. I've tried to make it as simple as possible, but there's only so much you can do. If you're having trouble, feel free to ask for help. The [RhostMUSH Discord](https://discord.gg/RxxtfVRYA4) is a good place to start.

**Q:** No, really, it's complicated man! And all I want to do is run a game!

**A:** Afraid them's the breaks. Being an owner and game runner of any sort of MU*, MMORPG server emulator, or heck, even a Minecraft server comes with it technical challenges. If you're not that technically inclined, then my advice is to find others who are, and share your vision.

**Q:** Do you take donations?

**A:** Yes! I have a [Patreon](https://www.patreon.com/volund) and a [PayPal](https://www.paypal.com/paypalme/volundmush).

**Q:** I want this `<cool system>` from `<other game>`. Can you add it?

**A:** Well that's basically how this whole project came around in the first place. I kept seeing `<cool systems>` from `<other games>` and kinda copied them. At the time, many of them were proprietary. I try to focus on code that's going to be super popular, super general, and useful to many games though, rather than something that's going to be specific to just one game. The best way to convince me to make something that's part of the main suite is to show me how it's going to be useful to a lot of games.