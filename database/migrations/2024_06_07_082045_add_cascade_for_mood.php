<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeForMood extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add onDelete cascade to mood_id foreign key
        DB::statement('ALTER TABLE moods_mood_feelings DROP CONSTRAINT moods_mood_feelings_mood_id_foreign');
        DB::statement('ALTER TABLE moods_mood_feelings ADD CONSTRAINT moods_mood_feelings_mood_id_foreign FOREIGN KEY (mood_id) REFERENCES moods(id) ON DELETE CASCADE');

        // Add onDelete cascade to mood_feeling_id foreign key
        DB::statement('ALTER TABLE moods_mood_feelings DROP CONSTRAINT moods_mood_feelings_mood_feeling_id_foreign');
        DB::statement('ALTER TABLE moods_mood_feelings ADD CONSTRAINT moods_mood_feelings_mood_feeling_id_foreign FOREIGN KEY (mood_feeling_id) REFERENCES mood_feelings(id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove onDelete cascade from mood_id foreign key
        DB::statement('ALTER TABLE moods_mood_feelings DROP CONSTRAINT moods_mood_feelings_mood_id_foreign');
        DB::statement('ALTER TABLE moods_mood_feelings ADD CONSTRAINT moods_mood_feelings_mood_id_foreign FOREIGN KEY (mood_id) REFERENCES moods(id)');

        // Remove onDelete cascade from mood_feeling_id foreign key
        DB::statement('ALTER TABLE moods_mood_feelings DROP CONSTRAINT moods_mood_feelings_mood_feeling_id_foreign');
        DB::statement('ALTER TABLE moods_mood_feelings ADD CONSTRAINT moods_mood_feelings_mood_feeling_id_foreign FOREIGN KEY (mood_feeling_id) REFERENCES mood_feelings(id)');
    }
}
