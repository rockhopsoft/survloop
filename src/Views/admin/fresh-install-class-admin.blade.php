/* SurvLoop-generated from resources/views/vendor/survloop/admin/fresh-install-class-admin.blade.php */

namespace App\Http\Controllers\{{ $abbr }};

use Illuminate\Http\Request;

use SurvLoop\Controllers\AdminController;

class {{ $abbr }}Admin extends AdminController
{
	
	public $classExtension 	= '{{ $abbr }}Admin';
	
	protected function initExtra(Request $request)
	{
		
	}
	
}