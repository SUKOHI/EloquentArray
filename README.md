# EloquentArray
A Laravel package to deal with array values that we can search through where clause.  
This package is only for Laravel 5+.

# Installation

Execute the following command.

    composer require sukohi/eloquent-array:2.*
    
then set EloquentArrayServiceProvider in your config/app.php.

    Sukohi\EloquentArray\EloquentArrayServiceProvider::class, 
    
# Preparation

Execute the following command to publish and migrate the migration.

    php artisan vendor:publish
    php artisan migrate
    
Then set `EloquentArrayTrait`, `$casts` and its events in your model like so.

    <?php
    
    namespace App;

    use Illuminate\Database\Eloquent\Model;
    use Sukohi\EloquentArray\EloquentArrayTrait;
    
    class RockBand extends Model
    {
        use EloquentArrayTrait;
        
        protected $casts = ['members' => 'array', 'instruments' => 'array'];
    
        public static function boot()
        {
            parent::boot();
    
            RockBand::saved(function($rock_band)
            {
                $rock_band->saveArrayItem();
            });
    
            RockBand::deleted(function($rock_band)
            {
                $rock_band->deleteArrayItem($rock_band->id);
            });
        }
        
* Note 1: In the above case, your table must have columns called `members` and `instruments` which both of the column type is `text`.
* Note 2: You can set column name(s) as the 1st argument in `saveArrayItem()` like so.  


    $rock_band->saveArrayItem('members');
    
    // Or
    
    $rock_band->saveArrayItem(['members', 'instruments']);
    
# Usage

### Save

    $rock_band = new \App\RockBand;
    $rock_band->name = 'The Beatles';
    $rock_band->members = [
        'John Lennon', 
        'Paul McCartney', 
        'George Harrison', 
        'Ringo Starr'
    ];
    $rock_band->instruments = [
        'guitar', 
        'bass guitar', 
        'keyboards', 
        'drums', 
        'harmonica', 
        'sitar', 
        'percussion'
    ];
    $rock_band->save();
    
    // Or

    $rock_band = \App\RockBand::find(1);
    $rock_band->instruments = ['guitar',  'drums', 'piano'];
    $rock_band->save();
    
### Delete

When you delete an Eloquent item, all of the array values related to the item will be removed automatically.

    \App\RockBand::where('id', 1)->delete();

### Retrieve

You can get the values as usual.  

    $rock_band = \App\RockBand::find(1);
    
    print_r($rock_band->members);
    print_r($rock_band->instruments);


# Where Clause

You can use whereArray() method to filter your data like so.

    $rock_bands = \App\RockBand::whereArray('instruments', 'drums')->get();
    
    // or
    
    $rock_bands = \App\RockBand::where('id', 1)
        ->orWhereArray('instruments', 'guitar')
        ->get();
        
# Refresh Array Values

    \App\RockBand::refreshArray(['members', 'instruments']);
    
    // Or
    
    \App\RockBand::refreshArray('members');
    
# License

This package is licensed under the MIT License.  
Copyright 2016 Sukohi Kuhoh