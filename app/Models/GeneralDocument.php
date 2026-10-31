<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralDocument extends Model
{
    use HasFactory;

    protected $table = 'public.general_documents';

    protected $fillable = [
        'program',
        'sub_category',
        'title',
        'regulation_code',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
        'downloads_count',
        'status',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'downloads_count' => 'integer',
        'status' => 'integer',
    ];

    /**
     * Get icon class based on file type / extension.
     */
    public function getFileIconAttribute(): string
    {
        $ext = strtolower($this->file_type);

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
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    /**
     * Check if file can be rendered directly inline in a quick preview modal.
     */
    public function getIsPreviewableAttribute(): bool
    {
        $ext = strtolower($this->file_type);

        return in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }

    /**
     * Program badge color class.
     */
    public function getProgramBadgeClassAttribute(): string
    {
        return match (strtoupper($this->program)) {
            'MBA' => 'badge-program-mba',
            'BBA' => 'badge-program-bba',
            default => 'badge-program-common',
        };
    }
}
