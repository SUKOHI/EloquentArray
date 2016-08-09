# EloquentArray
A Laravel package to deal with array values that we can search through where clause.  
This package is only for Laravel 5.2+.

# Installation

Execute the following command.

    composer require sukohi/eloquent-array:3.*
    
then set EloquentArrayServiceProvider in your config/app.php.

    Sukohi\EloquentArray\EloquentArrayServiceProvider::class, 
    
# Preparation

Execute the following command to publish and migrate the migration.

    php artisan vendor:publish
    php artisan migrate
    
Then set `EloquentArrayTrait` in your model like so.

    <?php
    
    namespace App;

    use Illuminate\Database\Eloquent\Model;
    use Sukohi\EloquentArray\EloquentArrayTrait;
    
    class RockBand extends Model
    {
        use EloquentArrayTrait;
    }

# Usage

### Save

    $rock_band->name = 'The Beatles';
    $rock_band->setArray('members', [
         'John Lennon', 
         'Paul McCartney', 
         'George Harrison', 
         'Ringo Starr'
    ]);
    $rock_band->save();
    $rock_band->saveArray();
    
    // Or

    $rock_band->name = 'The Beatles';
    $rock_band->setAllArray([
        'members' => [
            'John Lennon', 
            'Paul McCartney', 
            'George Harrison', 
            'Ringo Starr'
        ]
    ]);
    $rock_band->save();
    $rock_band->saveArray();
    
### Delete

A specific array values related to an item will be removed.

    $rock_band = \App\RockBand::find(1);
    $rock_band->deleteArray('members');
    
### Clear

All of the array values related to an item will be removed.

    $rock_band = \App\RockBand::find(1);
    $rock_band->clearArray();

### Retrieve

    $rock_band = \App\RockBand::find(1);
    $array = $rock_band->getArray('members');


# Where Clause

You can use whereArray() method to filter your data like so.

    $rock_bands = \App\RockBand::whereArray('instruments', 'drums')->get();
    
    // or
    
    $rock_bands = \App\RockBand::where('id', 1)
        ->orWhereArray('instruments', 'guitar')
        ->get();
    
# License

This package is licensed under the MIT License.  
Copyright 2016 Sukohi Kuhoh