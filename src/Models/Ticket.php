<?php

namespace Coderflex\LaravelTicket\Models;

use Coderflex\LaravelTicket\Concerns;
use Coderflex\LaravelTicket\Scopes\TicketScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mallria\Ticket\Enums\TicketPriority;
use Mallria\Ticket\Enums\TicketStatus;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Coderflex\LaravelTicket\Models\Ticket
 *
 * @property string $uuid
 * @property int $user_id
 * @property string $title
 * @property string $message
 * @property TicketPriority $priority
 * @property TicketStatus $status
 * @property bool $is_resolved
 * @property bool $is_locked
 * @property int $assigned_to
 * @property array $payload
 */
class Ticket extends Model
{
    use HashableId;
    use TicketScope;
    use Concerns\InteractsWithTickets;
    use Concerns\InteractsWithTicketRelations;

    const TABLE = 'tickets';

    protected $table = self::TABLE;
    protected $hasKey = self::TABLE;

    /**
     * 可批量赋值的字段
     *
     * @var array<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'message',
        'priority',
        'status',
        'is_resolved',
        'is_locked',
        'assigned_to',
        'payload', // 允许填充 payload
    ];

    /**
     * 字段类型转换
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => TicketPriority::class,
        'is_resolved' => 'boolean',
        'is_locked' => 'boolean',
        'payload' => 'array', // 确保 payload 自动转换为数组
    ];


    protected $appends = [
        'hash',
    ];

    public function getRouteKeyName()
    {
        return 'hash';
    }

    /**
     * Get User RelationShip
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Get Assigned To User RelationShip
     */
    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'assigned_to');
    }

    /**
     * Get Messages RelationShip
     */
    public function messages(): HasMany
    {
        $tableName = config('laravel_ticket.table_names.messages', 'messages');

        return $this->hasMany(
            config('laravel_ticket.models.Message'),
            (string)$tableName['columns']['ticket_foreing_id'],
        );
    }

    /**
     * Get Categories RelationShip
     */
    public function categories(): BelongsToMany
    {
        $table = config('laravel_ticket.table_names.category_ticket', 'category_ticket');

        return $this->belongsToMany(
            config('laravel_ticket.models.Category'),
            $table['table'],
            $table['columns']['ticket_foreign_id'],
            $table['columns']['category_foreign_id'],
        );
    }

    /**
     * Get Labels RelationShip
     */
    public function labels(): BelongsToMany
    {
        $table = config('laravel_ticket.table_names.label_ticket', 'label_ticket');

        return $this->belongsToMany(
            config('laravel_ticket.models.Label'),
            $table['table'],
            $table['columns']['ticket_foreign_id'],
            $table['columns']['label_foreign_id'],
        );
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return config(
            'laravel_ticket.table_names.tickets',
            parent::getTable()
        );
    }
}
