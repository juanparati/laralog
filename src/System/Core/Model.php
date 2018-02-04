<?php


/**
 * Model base class. All models should extend this class.
 *
 * @package    Mamuph Models
 * @category   Models
 * @author     Juan Lago
 * @copyright  (c) 2015-2016 Mamuph Team
 * @license    GNU/GPL
 */
abstract class Core_Model {

    /**
     * Create a new model instance.
     *
     * @example
     *
     *     $model = Model::factory($name);
     *
     * @param   string  $name   model name
     * @return  Model
     */
    public static function factory($name)
    {
        // Add the model prefix
        $class = 'Model_'.$name;

        return new $class;
    }

}