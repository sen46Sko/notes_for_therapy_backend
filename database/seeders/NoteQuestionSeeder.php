<?php

// database/seeders/NoteQuestionSeeder.php
namespace Database\Seeders;

use App\Models\NoteQuestion;
use Illuminate\Database\Seeder;

class NoteQuestionSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            'What inspired you today?',
            'What are your goals for this week?',
            'Describe a challenge you overcame recently.',
            'What are you grateful for?',
            'What\'s a new skill you\'d like to learn?',
            'Reflect on a recent accomplishment.',
            'What\'s your favorite memory from the past month?',
            'How can you improve your daily routine?',
            'What book or article has influenced you lately?',
            'Describe your ideal day.',
            'What\'s a habit you\'d like to develop?',
            'How do you practice self-care?',
            'What\'s a fear you\'d like to overcome?',
            'Describe a place that makes you feel peaceful.',
            'What\'s the best advice you\'ve ever received?',
            'How do you stay motivated?',
            'What\'s a goal you\'re working towards?',
            'Reflect on a mistake and what you learned from it.',
            'What\'s something new you\'d like to try?',
            'How do you define success?',
            'What\'s a relationship you\'d like to improve?',
            'Describe a recent act of kindness you witnessed or performed.',
            'What\'s your favorite way to relax?',
            'How do you want to make a difference in the world?',
            'What are you looking forward to in the near future?',
        ];

        foreach ($questions as $question) {
            NoteQuestion::create(['title' => $question]);
        }
    }
}
