<?php

namespace Modules\Loan\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Loan\Database\Factories\LoanFactory;
use Modules\User\App\Models\User;

class Loan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'interest',
        'term',
        'status',
    ];

    protected static function newFactory(): LoanFactory
    {
        return LoanFactory::new();
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'interest' => 'decimal:2',
            'term' => 'integer',
        ];
    }

    /**
     * The user who owns this loan.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
