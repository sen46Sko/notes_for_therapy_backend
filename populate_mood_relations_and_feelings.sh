#!/bin/bash

# Auth token (replace with your actual token)
AUTH_TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vZGkxNmhpb2JwM3FybC5jbG91ZGZyb250Lm5ldC9hcGkvbG9naW4iLCJpYXQiOjE3MTY4MjMxODIsImV4cCI6MTcxOTU4MzE4MiwibmJmIjoxNzE2ODIzMTgyLCJqdGkiOiI3MVdUaldRRTl5VzhtQ1RtIiwic3ViIjoiMyIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.PuRzsKDOWdE4dNTMKdG5UsGpeM_uomFXoYxKJfJS7oI"

# Base URL
BASE_URL="https://di16hiobp3qrl.cloudfront.net/api"

# Array of random mood feelings related to mental health
MOOD_FEELINGS=(
    "Happy" "Sad" "Anxious" "Calm" "Stressed" "Relaxed"
    "Excited" "Depressed" "Grateful" "Angry" "Peaceful" "Lonely"
    "Optimistic" "Pessimistic" "Content" "Nervous" "Energetic" "Tired"
    "Confident" "Scared" "Hopeful" "Frustrated" "Bored" "Motivated"
    "Inspired" "Worried" "Joyful" "Melancholic" "Relieved" "Focused"
)

# Array of random mood relations
MOOD_RELATIONS=(
    "Work" "Studying" "Exercise" "Socializing" "Family" "Leisure"
    "Traveling" "Shopping" "Cooking" "Cleaning" "Relaxing" "Reading"
    "Watching TV" "Playing Games" "Meditation" "Yoga" "Walking" "Running"
    "Cycling" "Hiking" "Swimming" "Dancing" "Volunteering" "Gardening"
    "Pet Care" "Art and Craft" "Music" "Driving" "Commuting" "Sleeping"
)

# Function to post data using curl
post_data() {
    local url=$1
    local data=$2
    curl -X POST "$url" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer $AUTH_TOKEN" \
    -d "$data"
}

# Insert mood feelings
for feeling in "${MOOD_FEELINGS[@]}"; do
    post_data "$BASE_URL/mood-feelings" "{\"name\": \"$feeling\"}"
done

# Insert mood relations
for relation in "${MOOD_RELATIONS[@]}"; do
    post_data "$BASE_URL/mood-relations" "{\"name\": \"$relation\"}"
done

echo "Inserted 30 random mood feelings and relations into the database."
