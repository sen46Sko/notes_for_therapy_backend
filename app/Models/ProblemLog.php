namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'action_type',
        'description',
        'value',
    ];

    public function ticket()
    {
        return $this->belongsTo(ProblemRequest::class, 'ticket_id');
    }
}
