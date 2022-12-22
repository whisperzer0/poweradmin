<?php

/*  Poweradmin, a friendly web-based admin tool for PowerDNS.
 *  See <https://www.poweradmin.org> for more details.
 *
 *  Copyright 2007-2010  Rejo Zenger <rejo@zenger.nl>
 *  Copyright 2010-2022  Poweradmin Development Team
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Script that handles deletion of supermasters
 *
 * @package     Poweradmin
 * @copyright   2007-2010 Rejo Zenger <rejo@zenger.nl>
 * @copyright   2010-2022  Poweradmin Development Team
 * @license     https://opensource.org/licenses/GPL-3.0 GPL
 */

use Poweradmin\BaseController;
use Poweradmin\DnsRecord;

require_once 'inc/toolkit.inc.php';
require_once 'inc/message.inc.php';

class DeleteSuperMasterController extends BaseController {

    public function run(): void
    {
        $this->checkPermission('supermaster_edit', ERR_PERM_DEL_SM);

        if (isset($_GET['confirm'])) {
            $this->deleteSuperMaster();
        } else {
            $this->showDeleteSuperMaster();
        }
    }

    private function deleteSuperMaster()
    {
        $v = new Valitron\Validator($_GET);
        $v->rules([
            'required' => ['master_ip', 'ns_name'],
            'ip' => ['master_ip'],
        ]);

        $master_ip = htmlspecialchars($_GET['master_ip']);
        $ns_name = htmlspecialchars($_GET['ns_name']);

        if ($v->validate()) {
             if (!DnsRecord::supermaster_ip_name_exists($master_ip, $ns_name)) {
                error(ERR_SM_NOT_EXISTS);
             }

            if (DnsRecord::delete_supermaster($master_ip, $ns_name)) {
                success(SUC_SM_DEL);

                $this->render('list_supermasters.html', [
                    'perm_sm_add' => do_hook('verify_permission', 'supermaster_add'),
                    'perm_sm_edit' => do_hook('verify_permission', 'supermaster_edit'),
                    'supermasters' => DnsRecord::get_supermasters()
                ]);
            }
        } else {
            $this->showErrors($v->errors());
        }
    }

    private function showDeleteSuperMaster()
    {
        $master_ip = htmlspecialchars($_GET['master_ip']);
        $info = DnsRecord::get_supermaster_info_from_ip($master_ip);

        $this->render('delete_supermaster.html', [
            'master_ip' => $master_ip,
            'info' => $info
        ]);
    }
}

$controller = new DeleteSuperMasterController();
$controller->run();
