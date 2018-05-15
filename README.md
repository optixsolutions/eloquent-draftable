# Laravel Draftable

Draft and publish your eloquent models.

```php
// Get published posts.
Post::all();

// Get all posts.
Post::withDrafts()->get();

// Get drafted posts.
Post::onlyDrafts()->get();
```

## Installation

You can install the package via composer:

```bash
composer require optix/draftable
```

## Usage

Add the following column to your model's table:

```php
$table->timestamp('published_at')->nullable();
```

Then use the `Optix\Draftable\Draftable` trait in your model.

```php
<?php

namespace App;

use Optix\Draftable\Draftable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Draftable;
}
```

Posts are "published" when the `published_at` column is not null and in the past.

Posts are "drafted" when the `published_at` column is null or in the future.

```php
Post::create([
    'published_at' => Carbon::now() // Published
    // Carbon::tomorrow() - Drafted until tomorrow
    // null - Indefinitely drafted
]);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
