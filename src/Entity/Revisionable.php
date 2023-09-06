<?php

namespace App\Entity;

interface Revisionable {
    public function isVvtInheriting(Team $team): array;
}
