<?php

namespace Optix\Draftable;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait Draftable
{
    /**
     * Register a global scope to only retrieve
     * published models in query results.
     *
     * @return void
     */
    public static function bootDraftable()
    {
        static::addGlobalScope('published', function (Builder $query) {
            $query
                ->where('published_at', '<=', Carbon::now())
                ->whereNotNull('published_at');
        });
    }

    /**
     * Scope to include draft models in query results.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeWithDrafts(Builder $query)
    {
        $query->withoutGlobalScope('published');
    }

    /**
     * Scope to only retrieve draft models in query results.
     *
     * @param Builder $query
     */
    public function scopeOnlyDrafts(Builder $query)
    {
        $query
            ->withDrafts()
            ->where('published_at', '>', Carbon::now())
            ->orWhereNull('published_at');
    }

    /**
     * Publish the model.
     *
     * @return void
     */
    public function publish()
    {
        $this->schedule(Carbon::now());
    }

    /**
     * Publish the model on a given date.
     *
     * @param Carbon $time
     * @return void
     */
    public function schedule(Carbon $time)
    {
        $this->setPublishedAt($time);
    }

    /**
     * Draft the model.
     *
     * @return void
     */
    public function draft()
    {
        $this->setPublishedAt(null);
    }

    /**
     * Update the "published_at" column value.
     *
     * @param mixed $publishedAt
     * @return void
     */
    protected function setPublishedAt($publishedAt)
    {
        $this->published_at = $publishedAt;

        $this->save();
    }

    /**
     * Determine if the model is published.
     *
     * @return bool
     */
    public function isPublished()
    {
        return (
            ! is_null($this->published_at)
            && $this->published_at <= Carbon::now()
        );
    }

    /**
     * Determine if the model is draft.
     *
     * @return bool
     */
    public function isDraft()
    {
        return ! $this->isPublished();
    }
}
