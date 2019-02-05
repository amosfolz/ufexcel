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
* Import data from files in `.csv` or `.xlsx` format
* Generate import templates
* Export data to `.pdf` `.hmtl`and `.xlsx`




### Important Information
Ufexcel writes to `php://output` by default. Please see [this link](https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/#redirect-output-to-a-clients-web-browser) on why this might not be safe for highly confidential files.

#### This Sprinkle overrides the following files: 

UserFrosting `core/templates`:
* `templates/table-tool-menu.html.twig`
* `templates/pages/partials/config.js.twig`

UserFrosting `admin/templates/pages`:
* `activities.html.twig`
* `dashboard.html.twig`
* `groups.html.twig`
* `permissions.html.twig`
* `roles.html.twig`
* `users.html.twig`


### Usage and Features


#### Config
ufexcel uses a custom configuration file found in `config/default.php`.


#### Permissions/Roles
ufexcel adds two permissions and two roles. 
* `Import` - allows user to import data as well as use the `template generator`. 
* `Export` - allows user to export data.
Note the `Export` permission does not impact the default "Download" User Frosting feature. ufexcel is not restricted by constraints setup in your Eloquent `model`, so take this into consideration when granting access to this feature. 


