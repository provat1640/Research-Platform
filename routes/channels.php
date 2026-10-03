<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('projects.{project}', function ($user, $project) {
    return $user->ownedProjects()->whereKey($project)->exists()
        || $user->researchProjects()->whereKey($project)->exists();
});
