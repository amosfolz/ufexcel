# ufexcel
Userfrosting 4 Sprinkle to import/export data.


***This is a work in progress***




### Installation

1. From within the `app/sprinkles` directory, clone using `git clone https://github.com/amosfolz/ufexcel.git ufexcel`

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



### Features
* Import data from files 
* Generate import templates
* Export data to `.pdf` `.hmtl`and `.xlsx`




### Important Information
Ufexcel writes to `php://output` by default. Please see [this link](https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/#redirect-output-to-a-clients-web-browser) on why this might not be safe for highly confidential files.

