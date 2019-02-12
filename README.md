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


#### Configuration Dashboard


##### Permissions/Roles
ufexcel adds a `UFExcel Administrator` role that provides authorization to view the configuration dashboard and manage configurations. 

##### Table Configurations
ufexcel uses a custom authorization extension to control table settings and user access to features.

You need to add a configuration for each client-side table for which UFExcel should be enabled. This consists of two parameters used to link your client-side table with your database table. 

* `tableid` - The ID set on your client-side table. If using ufTable, this is normally setup in your `page` template when you `include` your table: 
```
    <div class="box-body">
        {% include "tables/users-custom.html.twig" with {
                "table" : {
                    "id" : "table-members"
                }
            }
        %}
    </div>
 ```

* `dbtable` - The database table.

##### User Settings

After you create the table configuration you assign access to users Actions->Edit Users. 

![UserSettings](/screenshots/settings.png?raw=true "User Settings")

* `Import` - allows user to import data as well as use the `template generator`. 
* `Export` - allows user to export data.
Note the `Export` permission does not impact the default "Download" User Frosting feature. ufexcel is not restricted by constraints setup in your Eloquent `model`, so take this into consideration when granting access to this feature. 



#### Adding the dashboard to menu

Following the instructions at [this link](https://learn.userfrosting.com/recipes/advanced-tutorial/adding-menu) you can add this to your menu:
```
    {% if checkAccess('ufexcel_dashboard') %}
        <li>
            <a href="/ufexcel"><i class="fa fa-cog fa-fw"></i> <span>UFExcel Dashboard</span></a>
        </li>
    {% endif %}
```


#### Adding to custom tables

To add to additional tables, include `js/widgets/ufexcet` asset-bundle.
```
{% block scripts_page %}
    {# Add ufexcel widget #}
    {{ assets.js('js/widgets/ufexcel') | raw }}
{% endblock %}
```





