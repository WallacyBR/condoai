<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\CommonArea;
use App\Models\MaintenanceRecord;
use App\Models\Occurrence;
use App\Models\Package;
use App\Models\Pet;
use App\Models\Resident;
use App\Models\Visitor;
use Illuminate\Database\Seeder;

class DemoImageSeeder extends Seeder
{
    private const COMMON_AREA_PATH = 'common-areas/demo/common-area.png';

    private const PERSON_URL = '/storage/demo-images/person.png';

    private const PET_URL = '/storage/demo-images/pet.png';

    private const PACKAGE_URL = '/storage/demo-images/package.png';

    private const DOCUMENT_URL = '/storage/demo-images/document.png';

    private const CLASSIFIED_PATH = 'demo-images/classified.png';

    private const OCCURRENCE_PATH = 'demo-images/occurrence.png';

    private const MAINTENANCE_PATH = 'demo-images/maintenance.png';

    public function run(): void
    {
        $this->populateCommonAreas();
        $this->populatePeople();
        $this->populatePets();
        $this->populatePackages();
        $this->populateClassifieds();
        $this->populateOccurrences();
        $this->populateMaintenanceRecords();
    }

    private function populateCommonAreas(): void
    {
        CommonArea::query()->update([
            'main_photo' => self::COMMON_AREA_PATH,
            'additional_photos' => [self::COMMON_AREA_PATH],
        ]);
    }

    private function populatePeople(): void
    {
        Resident::query()->update(['photo' => self::PERSON_URL]);
        Visitor::query()->update(['photo_url' => self::PERSON_URL]);
    }

    private function populatePets(): void
    {
        Pet::query()->update(['photo' => self::PET_URL]);
    }

    private function populatePackages(): void
    {
        Package::query()->update([
            'photo' => self::PACKAGE_URL,
            'document_photo' => self::DOCUMENT_URL,
        ]);
    }

    private function populateClassifieds(): void
    {
        Ad::query()->each(function (Ad $ad): void {
            if ($ad->images()->exists()) {
                $ad->images()->update(['image_path' => self::CLASSIFIED_PATH]);

                return;
            }

            $ad->images()->create([
                'image_path' => self::CLASSIFIED_PATH,
                'order' => 0,
            ]);
        });
    }

    private function populateOccurrences(): void
    {
        Occurrence::query()->each(function (Occurrence $occurrence): void {
            if ($occurrence->photos()->exists()) {
                $occurrence->photos()->update(['photo_path' => self::OCCURRENCE_PATH]);

                return;
            }

            $occurrence->photos()->create([
                'photo_path' => self::OCCURRENCE_PATH,
                'description' => 'Imagem demonstrativa padrão da ocorrência.',
            ]);
        });
    }

    private function populateMaintenanceRecords(): void
    {
        MaintenanceRecord::query()->each(function (MaintenanceRecord $record): void {
            if ($record->photos()->exists()) {
                $record->photos()->update(['file_path' => self::MAINTENANCE_PATH]);

                return;
            }

            $record->photos()->create([
                'condominium_id' => $record->condominium_id,
                'file_path' => self::MAINTENANCE_PATH,
                'uploaded_at' => now(),
            ]);
        });
    }
}
