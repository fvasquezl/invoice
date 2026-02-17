<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $invoice_number
 * @property string $company_name
 * @property string|null $company_address
 * @property string|null $company_email
 * @property string|null $company_phone
 * @property string|null $company_logo
 * @property string $client_name
 * @property string|null $client_address
 * @property string|null $client_email
 * @property string|null $client_phone
 * @property \Illuminate\Support\Carbon $invoice_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property string|null $notes
 * @property string|null $terms
 * @property numeric $subtotal
 * @property numeric $tax_rate
 * @property numeric $tax_amount
 * @property numeric $total
 * @property int|null $template_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Template|null $template
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereClientAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereClientEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereClientPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCompanyAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCompanyEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCompanyLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCompanyPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUserId($value)
 * @mixin \Eloquent
 */
	class Invoice extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $invoice_id
 * @property string $description
 * @property int $quantity
 * @property numeric $unit_price
 * @property numeric $total
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class InvoiceItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $settings
 * @property int $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Template extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

