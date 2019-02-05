# ufexcel
Userfrosting 4 Sprinkle to import/export data.


***This is a work in progress***




### Installation

1. From the `app/sprinkles` directory you can clone using `git clone https://github.com/amosfolz/ufexcel.git ufexcel`

2. Edit UserFrosting `sprinkles.json` to include `ufexcel` in the `base` list. Example:
```    
"base": [
        "core",
        "account",
        "admin",
        "ufexcel"
    ]
```
3. Run `composer update` then `php bakery bake` to install the sprinkle.
