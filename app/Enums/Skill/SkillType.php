<?php

namespace App\Enums\Skill;

enum SkillType: int
{
    case COSMETIC = 0;          // Cosmetic or RP skills with no special features (need to be manually added to calculations)
    case CONSUMABLE = 1;        // Has charges which can be linked to activities or prompt entries (resets per designated time period)
    case ITEM_GRANTER = 2;      // Can be used to grant the character items (resets per designated time period)
}