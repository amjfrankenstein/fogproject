<?php
/**
 * The module class.
 *
 * PHP version 5
 *
 * @category Module
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * The module class.
 *
 * @category Module
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class Module extends FOGController
{
    /**
     * The module table name.
     *
     * @var string
     */
    protected $databaseTable = 'modules';
    /**
     * The module fields and common names.
     *
     * @var array
     */
    protected $databaseFields = [
        'id' => 'id',
        'name' => 'name',
        'shortName' => 'short_name',
        'description' => 'description',
        'isDefault' => 'default'
    ];
    /**
     * The required fields.
     *
     * @var array
     */
    protected $databaseFieldsRequired = [
        'name',
        'shortName'
    ];
    /**
     * Additional fields
     *
     * @var array
     */
    protected $additionalFields = [
        'hosts'
    ];
    /**
     * Alters valid method.
     *
     * @return bool
     */
    public function isValid()
    {
        return (bool)parent::isValid()
            && $this->get('shortName');
    }
    /**
     * Destroys the object.
     *
     * @param string $key the key to match for removal.
     *
     * @return bool|mixed
     */
    public function destroy($key = 'id')
    {
        Route::deletemass(
            'moduleassociation',
            ['moduleID' => $this->get('id')]
        );
        return parent::destroy($key);
    }
    /**
     * Loads any hosts this module has
     *
     * @return void
     */
    protected function loadHosts()
    {
        $find = ['moduleID' => $this->get('id')];
        Route::ids(
            'moduleassociation',
            [],
            'hostID'
        );
        $hosts = json_decode(Route::getData(), true);
        $this->set('hosts', (array)$hosts);
    }
    /**
     * Add host to the group.
     *
     * @param array $addArray the host to add
     *
     * @return object
     */
    public function addHost($addArray)
    {
        return $this->addRemItem(
            'hosts',
            (array)$addArray,
            'merge'
        );
    }
    /**
     * Remove host from the group.
     *
     * @param array $removeArray the host to remove
     *
     * @return object
     */
    public function removeHost($removeArray)
    {
        return $this->addRemItem(
            'hosts',
            (array)$removeArray,
            'diff'
        );
    }
    /**
     * Saves the group elements.
     *
     * @return object
     */
    public function save()
    {
        parent::save();
        return $this
            ->assocSetter('Module', 'host')
            ->load();
    }
}
