<?php namespace Winterpk\ContactBook;

use System\Classes\PluginBase;

/**
 * ContactBook Plugin Information File
 */
class Plugin extends PluginBase
{
	
	/**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.User'];
	
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Contact Book',
            'description' => 'A fully featured contact book for October CMS',
            'author'      => 'winterpk',
            'icon'        => 'icon-book'
        ];
    }
	
	public function registerComponents()
	{
		return [
			'Winterpk\ContactBook\Components\ContactBook' => 'contactbook',
		];
	}
	
	public function onLoad()
	{
		
	}
	
}
