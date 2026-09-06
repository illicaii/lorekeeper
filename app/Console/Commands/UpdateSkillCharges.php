<?php

namespace App\Console\Commands;

use App\Models\Character\CharacterSkill;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateSkillCharges extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-skill-charges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if there are any skill charges to update.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        //
        $updateAbilities = CharacterSkill::requiresUpdate()->get();
        foreach ($updateAbilities as $ability) {
            if ($ability->skill->reset_period() != null){
                $ability->update([
                    'charges'           => 0,
                    'reset_time'        => Carbon::now()->add(
                        $ability->skill->reset_frequency(),
                        $ability->skill->reset_period(),
                    )->startOf($ability->skill->reset_period()),
                ]);
            }
        }
    }
}
