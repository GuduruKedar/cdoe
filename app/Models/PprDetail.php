<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PprDetail extends Model
{
    use HasFactory;

    protected $table = 'visa.ppr_details';

    protected $primaryKey = 's_no';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'prog_id',
        'prog_name',
        'regname',
        'regcode',
        'filename',
        'filepath',
        'description',
    ];

    /**
     * Get icon class based on file type / extension.
     */
    public function getFileIconAttribute(): string
    {
        $ext = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION) ?: 'pdf');

        return match ($ext) {
            'pdf' => 'bi bi-file-earmark-pdf-fill text-danger',
            'doc', 'docx' => 'bi bi-file-earmark-word-fill text-primary',
            'xls', 'xlsx', 'csv' => 'bi bi-file-earmark-excel-fill text-success',
            'ppt', 'pptx' => 'bi bi-file-earmark-ppt-fill text-warning',
            'zip', 'rar', '7z', 'tar', 'gz' => 'bi bi-file-earmark-zip-fill text-secondary',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => 'bi bi-file-earmark-image-fill text-info',
            default => 'bi bi-file-earmark-text-fill text-secondary',
        };
    }

    /**
     * File extension.
     */
    public function getFileTypeAttribute(): string
    {
        return strtolower(pathinfo($this->filename, PATHINFO_EXTENSION) ?: 'pdf');
    }

    /**
     * Human-readable file size from disk.
     */
    public function getFormattedSizeAttribute(): string
    {
        try {
            if ($this->filepath && Storage::disk('public')->exists($this->filepath)) {
                $bytes = Storage::disk('public')->size($this->filepath);
                if ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 1).' MB';
                }
                if ($bytes >= 1024) {
                    return number_format($bytes / 1024, 1).' KB';
                }

                return $bytes.' B';
            }
        } catch (\Throwable $e) {
        }

        return 'PDF';
    }

    /**
     * Check if file can be rendered directly inline in a quick preview modal.
     */
    public function getIsPreviewableAttribute(): bool
    {
        $ext = $this->file_type;

        return in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }

    /**
     * Program badge styling class.
     */
    public function getProgramBadgeClassAttribute(): string
    {
        return match (strtoupper($this->prog_name)) {
            'MBA' => 'badge-program-mba',
            'MCA' => 'badge-program-mca',
            'BBA' => 'badge-program-bba',
            'BCA' => 'badge-program-bca',
            default => 'badge-program-common',
        };
    }
}
