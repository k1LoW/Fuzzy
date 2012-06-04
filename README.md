# Fuzzy plugin for CakePHP

Adjust fuzzy input!!

## Install

First, Install 'Fuzzy' by [recipe.php](https://github.com/k1LoW/recipe) , and set `CakePlugin::load('Fuzzy');`

Second, for example, add the following code in Post.php.

    <?php
       class Post extends AppModel {
           public $actsAs = array('Fuzzy.Adjustable');
           public $convertFields = array(
                                         array('field' => 'title',
                                               'mb_convert_kana' => 'a', // mb_convert_kana()
                                               'encoding' => 'UTF-8'),
                                         array('field' => 'zip',
                                               'postal_split' => array('zip1', 'zip2'), // split zip code
                                               ),
                                         );
       }

And see test case!

## License

under MIT License

### AdjustableBehavior::mb_str_replace() original license

    @package     mb_str_replace
    @version     Release 3
    @author      HiNa <hina@bouhime.com>
    @copyright   Copyright (C) 2006-2007,2011 by HiNa <hina@bouhime.com>. All rights reserved.
    @license     https://github.com/fetus-hina/mb_str_replace/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
    @link        http://fetus.k-hsu.net/document/programming/php/mb_str_replace.html
    @link        https://github.com/fetus-hina/mb_str_replace
