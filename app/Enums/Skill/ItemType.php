<?php

namespace App\Enums\Skill;

enum ItemType: int {
    case GRANT = 0;
    case SET = 1;
    case RESET = 2;
    case REVOKE = 3;
    case ADD = 4;
}
