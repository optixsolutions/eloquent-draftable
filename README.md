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

When the `Draftable` trait is included in a model, a global scope will be registered
to automatically exclude draft records from query results. Because of this, the trait
exposes two local scopes to allow these draft records to be queried.

```php
// Only retrieve published records...
$onlyPublished = Post::all();

// Retrieve draft & published records...
$withDrafts = Post::withDrafts()->get();

// Only retrieve draft records...
$onlyDrafts = Post::onlyDrafts()->get();
```

**Get the published status of model**

The trait exposes two methods for determining the published status of a model, both
of which return a `bool`.

```php
$post = Post::withDrafts()->first();

// Determine if the model is published...
$post->isPublished();

// Determine if the model is draft...
$post->isDraft();
```

**WIP**

```php
// Mark the model as published...
$post->setPublished(true); // Does not persist
$post->publish(); // or $post->publish(true);

// Mark the model as draft...
$post->setPublished(false); // Does not persist
$post->draft(); // or $post->publish(false);

// Schedule the model to be published...
$post->setPublishedAt('+1 week'); // Does not persist
$post->publishAt(Carbon::now()->addWeek());
```

## License

This package is licensed under the [MIT license](LICENSE.md).
