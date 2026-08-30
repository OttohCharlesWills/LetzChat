<?php

namespace App\Models\Concerns;

use App\Models\Hashtag;

trait HasHashtags
{
    public function hashtags()
    {
        return $this->morphToMany(Hashtag::class, 'hashtaggable');
    }
}