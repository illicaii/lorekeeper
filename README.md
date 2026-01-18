# libSkills_v3.0
This extension is still a heavy WIP. Most of the features listed below DO NOT EXIST YET.
**I wouldn't recommend you pull this extension at this moment. It's only here for my personal use.**

## Features
Skills function similarly to traits and are assigned to the active image of an uploaded character. This makes it possible to have different images preserve their stats independently (when reverting an image you can choose to carry over the newer stats).
Stats have the following features:
- Can create Skills, Skill Categories, *Researching Tasks
- Skill Categories can be marked as default skills which allows them to be auto added to MYOs or new character with the set level (or random level range)
- Skill Categories can define skill cap, skill charges (uses), max skill level and variables related to the level curve (xp base and multiplier)
- Skills can be set to override Skill Category defaults for skill cap, skill charges (uses), max skill level and variables related to the level curve (xp base and multiplier)
- Skills can be awarded through Grants, Items, Prompts/Claims, Loot tables, Researching*
- Skills can gain xp or levels through Grants, Items, Prompts/Claims, Loot tables, Training*
- Skills can be three types: Cosmetic, Consumable, Item Granter
⋅⋅⋅⋅* Cosmetic: Mostly for RP but may also be used for RNG events or as parent skills for other skills
⋅⋅⋅⋅* Consumable: Has a set number of charges which can be linked to activities like prompt entry (think Stamina). These skills can be set to reset hourly/daily/monthly/yearly/never
⋅⋅⋅⋅* Item Granter: Can be used to grant the user x items. This action can be set to reset hourly/daily/monthly/yearly/never
- Skills can be reset, added, or removed by staff with character editing privileges on a per character basis from the skill edit menu on the character page
- Skills can be added to character uploads by staff or on MYO submissions by users.

- *Researching is a new activity where you can assign characters to it for x amount of time learn y skill. Researching tasks can be set up by admin with duration and reward. These can also be used to award items/currency/loot tables (or possibly linked other extensions to award themes, recipes, achievements). Recipes can be given level, item, or cost requirements. Number of concurrent researches can be set in the site settings.
- *Training is a new activity where you can assign characters to a slot for x amount of time to earn y amount of xp, levels, items, or currency. Amount of free training slots and purchasable training slots can be set in site settings. (Training slots are awarded through slot items or manual grants)

## Concerns
- libskills may conflict with character transformations or other extensions which rely on using different character images. Its likely you'll have to do some work to get them to work properly. Similarly if you want to integrate skills in to other extensions you will have to do so yourself.
- This extension is untested on LARGE character databases. It should be fine, however if you encounter issues or slowness please reach out to Vilagyent on discord.

# Lorekeeper

Lorekeeper is a framework for managing deviantART-based ARPGs/closed species masterlists coded using the Laravel framework. In simple terms - you will be able to make a copy of the site, do some minor setup/enter data about your species and game, and it'll provide you with the automation to keep track of your species, players and ARPG submissions.

- Demo site: [http://lorekeeper.me/](http://lorekeeper.me/)
- Wiki: [http://wiki.lorekeeper.me](http://wiki.lorekeeper.me/index.php?title=Main_Page)

# Features

- Users can create an account which will hold their characters and earnings from participating in the game.
- Mods can add characters to the masterlist, which can also record updates to a character's design. (Yes, multiple mods can work on the masterlist at the same time.)
- Characters get a little bio section on their profile that their owners can edit. Personalisation!
- Users' ownership histories (including whether they are an FTO) and characters' previous owners are tracked.
- Users can submit art to the submission queue, which mods can approve/reject. This dispenses rewards automagically.
- Users can spend their hard-earned rewards immediately, without requiring mods to look over their trackers (because it's all been pre-approved).
- Characters, items and currency can be transferred between users. Plus...secure trading between users for game items/currency/characters on-site is also a thing.
- Logs for all transfers are kept, so it's easy to check where everything went.
- The masterlist is king, so ownership can't be ambiguous, and the current design of a character is always easily accessible.
- Speaking of which, you can search for characters based on traits, rarity, etc. Also, trait/item/etc. data get their own searchable lists - no need to create additional pages detailing restrictions on how a trait should be drawn/described.
- Unless you want to, in which case you can add custom pages in HTML without touching the codebase!
- A raffle roller for consecutive raffles! Mods can add/remove tickets and users who have already won something will be automatically removed from future raffles in the sequence.
- ...and more! Please refer to the [Wiki](http://wiki.lorekeeper.me/index.php?title=Category:Documentation) for more information and instructions for usage.

# Setup

Important: For those who are not familiar with web dev, please refer to the [Wiki](http://wiki.lorekeeper.me/index.php?title=Tutorial:_Setting_Up) for a much more detailed set of instructions!!

## Obtain a copy of the code

```
$ git clone https://github.com/corowne/lorekeeper.git
```

## Configure .env in the directory

```
$ cp .env.example .env
```

Client ID and secret for at least one supported social media platform are required for this step. See [the Wiki](http://wiki.lorekeeper.me/index.php?title=Category:Social_Media_Authentication) for platform-specific instructions.

Add the following to .env, filling them in as required (also fill in the rest of .env where relevant):
```
CONTACT_ADDRESS=(contact email address)
DEVIANTART_ACCOUNT=(username of ARPG group account)
```

## Setting up

Composer install:
```
$ composer install
```

Generate app key and run database migrations:
```
$ php artisan key:generate
$ php artisan migrate
```

Add basic site data:
```
$ php artisan add-site-settings
$ php artisan add-text-pages
$ php artisan copy-default-images
```

Finally, set up the admin account for logging in:
```
$ php artisan setup-admin-user
```

You will need to send yourself the verification email and then link your social media account as prompted.

## Contact

If you have any questions, please feel free to ask in the Discord server: https://discord.gg/U4JZfsu
