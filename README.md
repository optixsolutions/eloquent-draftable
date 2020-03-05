# Eloquent Draftable

Add draftable functionality to your eloquent models.

## Installation

```bash
composer require optix/eloquent-draftable
```

## Setup

**Step 1**

Add a nullable timestamp `published_at` column to your model's database table.

```php
$table->timestamp('published_at')->nullable();
```

**Step 2**

Include the `Optix\Draftable\Draftable` trait in your model.

```php
class Post extends Model
{
    use Draftable;
}
```

## Usage

**Todo:** More detailed description for each set of methods...

```php
// Only retrieve published records...
$onlyPublished = Post::all();

// Retrieve draft & published records...
$withDrafts = Post::withDrafts()->get();

// Only retrieve draft records...
$onlyDrafts = Post::onlyDrafts()->get();  

$post = Post::withDrafts()->first();

// Determine if the model is draft...
$post->isDraft();

// Determine if the model is published...
$post->isPublished();

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

This library is licensed under the [MIT license](LICENSE.md).
