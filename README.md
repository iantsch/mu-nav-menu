# WordPress Menu Walker with BEM classes

WordPress must-use plugin to register a custom extended front-end menu walker and new wrapper function for BEM styled CSS classes.

## Installation

You can install this must-use plugin two ways

### Via Composer

If you load your dependenies via Composer you can load this plugin with

```sh
$ php composer require iantsch/mu-nav-menu
```

### Via Download

Download/fork this repository and copy the contents of this must-use plugin into `wp-content/mu-plugins/`. 
If you visit your Plugin section in the `wp-admin` area, you should be able to see a newly created category (Must use) plugins

## Usage

A ready-to-use wrapper function `bem_nav_menu` for `wp_nav_menu` is included 

```php
$args = array(
    'base_class' => 'main-menu', // Optional. Default: menu
    'theme_location' => 'main-menu'
);
bem_nav_menu($args);
```

You have an additional argument to name your block to your convenience with the argument `base_class`.

## Filter Hooks

Yes, you can easily adapt the functionality of this walker with the already known filter hooks and a few custom ones.

### MBT/WalkerNavMenu/renderToggle - string $title

| Parameter | Default | Functionality |
|  --- | --- | --- | 
| `boolean $render` | true | Enables the rendering of an no-JS toggle with radioboxes |

### MBT/WalkerNavMenu/menuToggleTitle

| Parameter | Default | Functionality |
|  --- | --- | --- |
| `string $title` | title attribute for toggle anchor | To localize this string add a filter. |

### MBT/WalkerNavMenu/menuToggleContent

| Parameter | Default | Functionality |
|  --- | --- | --- | 
| `string $content` | string of a caret SVG | An additional toggle item for nested menus. |

### MBT/WalkerNavMenu/autoArchiveMenu

| Parameter | Default | Functionality |
|  --- | --- | --- | 
| `boolean $render, int $depth, object $item` | false | Enables an automated post type archive sub menu |

### MBT/WalkerNavMenu/autoTaxonomyMenu

| Parameter | Default | Functionality |
|  --- | --- | --- | 
| `boolean $render, int $depth, object $item` | false | Enables an automated posts per term of taxonomy sub menu |

###  MBT/WalkerNavMenu/PostTypeArchive/queryArgs/postType={$postType} 

| Parameter | Default | Functionality |
|  --- | --- | --- | 
| `array $query_args` | [see below](#default-post-type-archive-arguments) | Adapt the automated sub menu query for $postType |

#### Default post type archive arguments

```php
array(
    'post_type' => $item->object,
    'posts_per_page' => -1,
    'post_parent' => 0,
)
```

### MBT/WalkerNavMenu/TermChildren/queryArgs/taxonomy={$taxonomy} 

| Parameter | Default | Functionality |
|  --- | --- | --- | 
| `array $query_args` | [see below](#default-term-children-arguments) | Adapt the automated sub menu query for $taxonomy |


#### Default term children arguments

```php
array(
    'post_type' => $taxonomy->object_type,
    'posts_per_page' => -1,
    'post_parent' => 0,
    'tax_query' => array(
        array(
            'taxonomy' => $item->object,
            'field' => 'id',
            'terms' => $item->object_id
        )
    )
)
```

### MBT/WalkerNavMenu/mobileMenuContent

| Parameter | Default | Functionality |
|  --- | --- | --- |
| `string $content` | string of html for the mobile toggle | An additional toggle item for mobile menus (burger). |


## Credits
[@iantsch](https://twitter.com/iantsch) - [web developer](https://mbt.wien) behind this and other projects.
