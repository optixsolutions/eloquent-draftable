# Eloquent Draftable

Add draftable functionality to your eloquent models.

## Installation

You can install this package via composer.

```bash
composer require optix/eloquent-draftable
```

## Setup

1. Add a nullable timestamp `published_at` column to your model's database table.

    ```php
    $table->timestamp('published_at')->nullable();
    ```

2. Include the `Optix\Draftable\Draftable` trait in your model.

    ```php
    class Post extends Model
    {
        use Draftable;
    }
    ```

## Usage

**Query scopes**

When the `Draftable` trait is included in a model, a global scope will be registered to automatically exclude
draft records from query results. Therefore, in order to query draft records you must apply one of the local
scopes outlined below.

```php
// Only retrieve published records...
$onlyPublished = Post::all();

// Retrieve draft & published records...
$withDrafts = Post::withDrafts()->get();

// Only retrieve draft records...
$onlyDrafts = Post::onlyDrafts()->get();
```

**Publish a model**

```php
$post = Post::withDrafts()->first();

// Publish without saving...
$post->setPublished(true);

// Publish and save...
$post->publish(); // or $post->publish(true);
```

When you attempt to publish a model that's already been published, the `published_at` timestamp will not be updated.

**Draft a model**

```php
// Draft without saving...
$post->setPublished(false);

// Draft and save...
$post->draft(); // or $post->publish(false);
```

**Schedule a model to be published**

```php
$publishDate = Carbon::now()->addWeek();
// $publishDate = '2020-01-01 00:00:00';
// $publishDate = '+1 week';

// Schedule without saving...
$post->setPublishedAt($publishDate);

// Schedule and save...
$post->publishAt($publishDate);
```

The methods outlined above both require a `$date` parameter of type `DateTimeInterface|string|null`.

**Get the published status of a model**

```php
// Determine if the model is published...
$post->isPublished();

// Determine if the model is draft...
$post->isDraft();
```

## License

This package is licensed under the [MIT license](LICENSE.md).
