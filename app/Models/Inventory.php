<?php
namespace App\Models;
use App\Models\InventoryHead;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Inventory extends Model
{
    use HasFactory;
    protected $table = 'inventories';
    protected $fillable = [
        'school_id',
        'inventory_head_id',
        'name',
        'condition',
        'costprice',
        'tax',
        'specs_details',
        'guess_life',
        'sources_id',
        'tax_free_amount',
        'tax_free_details',
        'depreciation_percentage',
        'other_details',
        'land_area',
        'land_type',
        'land_costprice',
        'land_owner_certificate_no',
        'land_location',
        'land_kitta_no',
        'if_donation',
        'land_market_value',
        'if_physical_structure_there',
        'physical_structure_detail',
    ];
    protected $casts = [
        'if_donation' => 'boolean',
        'if_physical_structure_there' => 'boolean',
    ];
    
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function inventoryHead()
    {
        return $this->belongsTo(InventoryHead::class, 'inventory_head_id');
    }

    public function source()
    {
        return $this->belongsTo(Source::class, 'sources_id');
    }
}