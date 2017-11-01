<?php
/**
 * Manager class for Hosts.
 *
 * PHP Version 5
 *
 * @category HostManager
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * Manager class for Hosts.
 *
 * @category HostManager
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class HostManager extends FOGManagerController
{
    /**
     * The base table name.
     *
     * @var string
     */
    public $tablename = 'hosts';
    /**
     * Install our table.
     *
     * @return bool
     */
    public function install()
    {
        $this->uninstall();
        $sql = Schema::createTable(
            $this->tablename,
            true,
            array(
                'hostID',
                'hostName',
                'hostDesc',
                'hostIP',
                'hostImage',
                'hostBuilding',
                'hostCreateDate',
                'hostCreateBy',
                'hostLastDeploy',
                'hostUseAD',
                'hostADDomain',
                'hostADOU',
                'hostADUser',
                'hostADPass',
                'hostADPassLegacy',
                'hostProductKey',
                'hostPrinterLevel',
                'hostKernelArgs',
                'hostKernel',
                'hostDevice',
                'hostInit',
                'hostPending',
                'hostPubKey',
                'hostSecToken',
                'hostSecTime',
                'hostPingCode',
                'hostExitBios',
                'hostExitEfi',
                'hostEnforce',
            ),
            array(
                'INTEGER',
                'VARCHAR(16)',
                'LONGTEXT',
                'VARCHAR(25)',
                'INTEGER',
                'INTEGER',
                'TIMESTAMP',
                'VARCHAR(40)',
                'DATETIME',
                "ENUM('0', '1')",
                'VARCHAR(255)',
                'LONGTEXT',
                'VARCHAR(255)',
                'VARCHAR(255)',
                'LONGTEXT',
                'LONGTEXT',
                'VARCHAR(2)',
                'VARCHAR(255)',
                'VARCHAR(255)',
                'VARCHAR(255)',
                'LONGTEXT',
                "ENUM('0', '1')",
                'LONGTEXT',
                'LONGTEXT',
                'TIMESTAMP',
                'VARCHAR(20)',
                'LONGTEXT',
                'LONGTEXT',
                "ENUM('0', '1')"
            ),
            array(
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false
            ),
            array(
                false,
                false,
                false,
                false,
                false,
                false,
                'CURRENT_TIMESTAMP',
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                '0000-00-00 00:00:00',
                false,
                false,
                false,
                '1'
            ),
            array(
                'hostID',
                'hostName'
            ),
            'MyISAM',
            'utf8',
            'hostID',
            'hostID'
        );
    }
    /**
     * Try to find a unique host object based on MAC addresses.
     *
     * @param array $macs the macs to search for the host
     *
     * @throws Exception
     *
     * @return void
     */
    public function getHostByMacAddresses($macs)
    {
        self::$Host = new Host();
        $MACHost = self::getSubObjectIDs(
            'MACAddressAssociation',
            array(
                'pending' => array(0, ''),
                'mac' => $macs,
            ),
            'hostID'
        );
        if (count($MACHost) < 1) {
            return;
        }
        if (count($MACHost) > 1) {
            $MACHost = self::getSubObjectIDs(
                'MACAddressAssociation',
                array(
                    'pending' => array(0, ''),
                    'primary' => 1,
                    'mac' => $macs
                ),
                'hostID'
            );
            if (count($MACHost) > 1) {
                throw new Exception(self::$foglang['ErrorMultipleHosts']);
            }
        }
        self::$Host = new Host(@max($MACHost));
        return;
    }
    /**
     * Try to find a unique host object based on UUID, system serial and mainboard serial.
     *
     * @param string $sysuuid
     * @param string $sysserial
     * @param string $mbserial
     *
     * @throws Exception
     *
     * @return void
     */
    public static function getHostByUuidAndSerial($sysuuid, $sysserial, $mbserial)
    {
        self::$Host = new Host();
/* Can probably be removed but will keep this list for now in case we need it.
        $invalidUuids = array(
            '00020003-0004-0005-0006-000700080009',
            '00000000-0000-0000-0000-000000000000',
//            '00000000-0000-0000-0000-*',
            '12345678-1234-5678-90AB-CDDEEFAABBCC',
            'FFFFFFFF-FFFF-FFFF-FFFF-FFFFFFFFFFFF',
            'FFFFFF00-FFFF-FFFF-FFFF-FFFFFFFFFFFF',
            'Not Present',
            'Not Settable'
        );
        $invalidMbSerial = array(
            'Type2 - Board Serial Number',
            'To be filled by O.E.M.',
            'Not Applicable',
            'Default string',
            'Base Board Serial Number',
            '.PCIE2' // Acer Phonix BIOS, https://www.driverscloud.com/en/configuration/90329-1/summary
        );
        $invalidSysSerial = array(
            '123456789'
        );
*/
        Route::listem('inventory',
            'sysuuid',
            false,
            array('sysuuid' => $sysuuid, 'sysserial' => $sysserial, 'mbserial' => $mbserial)
        );
        $Inventories = json_decode(Route::getData());
        throw new Exception($Inventories);
        $Inventories = $Inventories->inventorys;
        if (count($Inventories) == 1) {
            self::$Host = new Host($Inventories->hostID);
            return;
        }
        Route::listem('inventory',
            'sysuuid',
            false,
            array('sysuuid' => $sysuuid, 'sysserial' => $sysserial)
        );
        $Inventories = json_decode(Route::getData());
        $Inventories = $Inventories->inventorys;
        if (count($Inventories) == 1) {
            self::$Host = new Host($Inventories->hostID);
            return;
        }
        Route::listem('inventory',
            'sysuuid',
            false,
            array('sysuuid' => $sysuuid, 'mbserial' => $mbserial)
        );
        $Inventories = json_decode(Route::getData());
        $Inventories = $Inventories->inventorys;
        if (count($Inventories) == 1) {
            self::$Host = new Host($Inventories->hostID);
            return;
        }
        Route::listem('inventory',
            'sysserial',
            false,
            array('sysserial' => $sysserial, 'mbserial' => $mbserial)
        );
        $Inventories = json_decode(Route::getData());
        $Inventories = $Inventories->inventorys;
        if (count($Inventories) == 1) {
            self::$Host = new Host($Inventories->hostID);
            return;
        }
        Route::listem('inventory',
            'sysuuid',
            false,
            array('sysuuid' => $sysuuid)
        );
        $Inventories = json_decode(Route::getData());
        $Inventories = $Inventories->inventorys;
        if (count($Inventories) == 1) {
            self::$Host = new Host($Inventories->hostID);
            return;
        }
        Route::listem('inventory',
            'sysserial',
            false,
            array('sysserial' => $sysserial)
        ); 
        $Inventories = json_decode(Route::getData());
        $Inventories = $Inventories->inventorys;
        if (count($Inventories) == 1) {
            self::$Host = new Host($Inventories->hostID);
            return;
        }
        Route::listem('inventory',
            'mbserial',
            false,
            array('mbserial' => $mbserial)
        ); 
        $Inventories = json_decode(Route::getData());
        $Inventories = $Inventories->inventorys;
        if (count($Inventories) == 1) {
            self::$Host = new Host($Inventories->hostID);
            return;
        }
        return;
    }
    /**
     * Try to find a unique host object based on system serial.
     *
     * @param string $sysserial
     *
     * @throws Exception
     *
     * @return void
     */
    public static function getHostBySystemSerial($sysserial)
    {
        return;
    }
    /**
     * Try to find a unique host object based on mainboard serial.
     *
     * @param string $mbserial
     *
     * @throws Exception
     *
     * @return void
     */
    public static function getHostByMainboardserial($mbserial)
    {
        return;
    }
    /**
     * Removes fields.
     *
     * Customized for hosts
     *
     * @param array  $findWhere     What to search for
     * @param string $whereOperator Join multiple where fields
     * @param string $orderBy       Order returned fields by
     * @param string $sort          How to sort, ascending, descending
     * @param string $compare       How to compare fields
     * @param mixed  $groupBy       How to group fields
     * @param mixed  $not           Comparator but use not instead
     *
     * @return parent::destroy
     */
    public function destroy(
        $findWhere = array(),
        $whereOperator = 'AND',
        $orderBy = 'name',
        $sort = 'ASC',
        $compare = '=',
        $groupBy = false,
        $not = false
    ) {
        /*
         * Remove the main host items
         */
        parent::destroy(
            $findWhere,
            $whereOperator,
            $orderBy,
            $sort,
            $compare,
            $groupBy,
            $not
        );
        /*
         * Setup for removing associative areas
         */
        if (isset($findWhere['id'])) {
            $findWhere = array('hostID' => $findWhere['id']);
        }
        /**
         * Get the snapin job ids associated.
         */
        $SnapinJobIDs = array(
            'jobID' => self::getSubObjectIDs(
                'SnapinJob',
                $findWhere
            ),
        );
        /*
         * Remove any host node failure entries
         */
        self::getClass('NodeFailureManager')->destroy($findWhere);
        /*
         * Remove imaging log entries
         */
        self::getClass('ImagingLogManager')->destroy($findWhere);
        /*
         * Remove any snapin task entries
         */
        self::getClass('SnapinTaskManager')->destroy($SnapinJobIDs);
        /*
         * Remove any snapin job entries
         */
        self::getClass('SnapinJobManager')->destroy($findWhere);
        /*
         * Remove any task entries
         */
        self::getClass('TaskManager')->destroy($findWhere);
        /*
         * Remove any scheduled task entries
         */
        self::getClass('ScheduledTaskManager')->destroy($findWhere);
        /*
         * Remove any auto logout entries
         */
        self::getClass('HostAutoLogoutManager')->destroy($findWhere);
        /*
         * Remove any host screen entries
         */
        self::getClass('HostScreenSettingManager')->destroy($findWhere);
        /*
         * Remove any group entries
         */
        self::getClass('GroupAssociationManager')->destroy($findWhere);
        /*
         * Remove any snapin entries
         */
        self::getClass('SnapinAssociationManager')->destroy($findWhere);
        /*
         * Remove any printer entries
         */
        self::getClass('PrinterAssociationManager')->destroy($findWhere);
        /*
         * Remove any module entries
         */
        self::getClass('ModuleAssociationManager')->destroy($findWhere);
        /*
         * Remove any green fog entries
         */
        self::getClass('GreenFogManager')->destroy($findWhere);
        /*
         * Remove any inventory entries
         */
        self::getClass('InventoryManager')->destroy($findWhere);
        /*
         * Remove any user tracking entries
         */
        self::getClass('UserTrackingManager')->destroy($findWhere);
        /*
         * Remove any mac association entries
         */
        self::getClass('MACAddressAssociationManager')->destroy($findWhere);
        /*
         * Remove any power management entries
         */
        self::getClass('PowerManagementManager')->destroy($findWhere);
    }
}
