<?php

namespace App\Models;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'google_maps_url',
        'complaint_email',
        'qr_code',
        'feedback_url',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function getQrCodeUrl(): string
    {
        return url('review/' . $this->feedback_url);
    }

    public function generateQrCode(): string
    {
        $qrCode = new QrCode($this->getQrCodeUrl());
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        Storage::disk('public')->makeDirectory('qr-codes');
        $filename = 'qr-codes/' . $this->id . '-' . $this->feedback_url . '.png';
        Storage::disk('public')->put($filename, $result->getString());

        return $filename;
    }

    public function getQrCodePath(): string
    {
        if ($this->qr_code && Storage::disk('public')->exists($this->qr_code)) {
            return asset('storage/' . $this->qr_code);
        }
        return '';
    }
}
