<?php

namespace Database\Seeders;

use App\Models\Corp;
use App\Models\CorpEducationRequirement;
use App\Models\EducationLevel;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GafStructureSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('corp_education_requirements')->truncate();
        DB::table('applicant_corp_selections')->truncate();
        DB::table('corps')->truncate();
        DB::table('education_levels')->truncate();
        DB::table('sectors')->truncate();
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER SEQUENCE sectors_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE education_levels_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE corps_id_seq RESTART WITH 1');
            DB::statement('ALTER SEQUENCE corp_education_requirements_id_seq RESTART WITH 1');
        }
        Schema::enableForeignKeyConstraints();

        // ─── Education Levels ───
        $wasce = EducationLevel::create(['name' => 'WASSCE/SSSCE', 'slug' => 'wasce', 'rank' => 1, 'min_age' => 18, 'max_age' => 25]);
        $diploma = EducationLevel::create(['name' => 'Diploma/HND', 'slug' => 'diploma-hnd', 'rank' => 2, 'min_age' => 18, 'max_age' => 27]);
        $degree = EducationLevel::create(['name' => "Bachelor's Degree", 'slug' => 'degree', 'rank' => 3, 'min_age' => 18, 'max_age' => 26]);
        $masters = EducationLevel::create(['name' => "Master's Degree", 'slug' => 'masters', 'rank' => 4, 'min_age' => 21, 'max_age' => 30]);
        $doctorate = EducationLevel::create(['name' => 'Doctorate', 'slug' => 'doctorate', 'rank' => 5, 'min_age' => 25, 'max_age' => 40]);

        // ─── Sectors ───
        $combatOps = Sector::create(['name' => 'Combat Operations', 'slug' => 'combat-operations', 'description' => 'Frontline combat roles including infantry, armour, artillery and military police.', 'sort_order' => 1]);
        $engineering = Sector::create(['name' => 'Engineering', 'slug' => 'engineering', 'description' => 'Civil, mechanical, electrical, geomatic and other engineering disciplines across all services.', 'sort_order' => 2]);
        $itComms = Sector::create(['name' => 'IT & Communications', 'slug' => 'it-communications', 'description' => 'Signals, information technology, cyber security and communication systems.', 'sort_order' => 3]);
        $medical = Sector::create(['name' => 'Medical & Health', 'slug' => 'medical-health', 'description' => 'Healthcare professionals including doctors, nurses, pharmacists and allied health workers.', 'sort_order' => 4]);
        $adminFinance = Sector::create(['name' => 'Administration & Finance', 'slug' => 'administration-finance', 'description' => 'Pay corps, records, clerical duties and general administration.', 'sort_order' => 5]);
        $logistics = Sector::create(['name' => 'Logistics & Supply', 'slug' => 'logistics-supply', 'description' => 'Supply chain, transport, ordnance, catering and quartermaster services.', 'sort_order' => 6]);
        $educationSector = Sector::create(['name' => 'Education & Training', 'slug' => 'education-training', 'description' => 'Education corps and physical training/sports instruction.', 'sort_order' => 7]);
        $mediaPR = Sector::create(['name' => 'Media & Public Relations', 'slug' => 'media-public-relations', 'description' => 'Public relations, journalism, graphic design and media production.', 'sort_order' => 8]);
        $religious = Sector::create(['name' => 'Religious Affairs', 'slug' => 'religious-affairs', 'description' => 'Chaplaincy and Islamic affairs for spiritual and pastoral care.', 'sort_order' => 9]);
        $musicBand = Sector::create(['name' => 'Music & Band', 'slug' => 'music-band', 'description' => 'Regimental band, dance band and steel band musicians.', 'sort_order' => 10]);
        $trades = Sector::create(['name' => 'Technical Trades', 'slug' => 'technical-trades', 'description' => 'Vocational and technical trades including welding, carpentry, tailoring and mechanics.', 'sort_order' => 11]);
        $aviation = Sector::create(['name' => 'Aviation', 'slug' => 'aviation', 'description' => 'Air force operations, pilot training and aerospace engineering.', 'sort_order' => 12]);
        $maritime = Sector::create(['name' => 'Maritime', 'slug' => 'maritime', 'description' => 'Navy executive, technical and engineering roles for maritime operations.', 'sort_order' => 13]);
        $agriculture = Sector::create(['name' => 'Agriculture', 'slug' => 'agriculture', 'description' => 'Agricultural extension and farming services.', 'sort_order' => 14]);
        $legal = Sector::create(['name' => 'Legal', 'slug' => 'legal', 'description' => 'Legal officers providing legal advisory and prosecution services.', 'sort_order' => 15]);

        // ─── Helper ───
        $req = function ($corp, $educationLevel, $group, $specific = null, $certs = null, $grade = null) {
            CorpEducationRequirement::create([
                'corp_id' => $corp->id,
                'education_level_id' => $educationLevel->id,
                'degree_field_group' => $group,
                'specific_degrees' => $specific,
                'additional_certs' => $certs,
                'min_grade' => $grade,
            ]);
        };

        // ─── ARMY CORPS ───

        // 1. Infantry / Armour / Artillery / ASOB
        $infantry = Corp::create(['name' => 'Infantry', 'slug' => 'infantry', 'sector_id' => $combatOps->id, 'service' => 'army', 'description' => 'Frontline combat soldiers responsible for ground warfare and close combat operations.']);
        $req($infantry, $wasce, 'any');
        $req($infantry, $degree, 'any', null, null, 'second_class_lower');
        $req($infantry, $masters, 'any');
        $req($infantry, $doctorate, 'any');

        $armour = Corp::create(['name' => 'Armour', 'slug' => 'armour', 'sector_id' => $combatOps->id, 'service' => 'army', 'description' => 'Tank and armoured vehicle crews for mechanised warfare.']);
        $req($armour, $wasce, 'any');
        $req($armour, $degree, 'any', null, null, 'second_class_lower');
        $req($armour, $masters, 'any');

        $artillery = Corp::create(['name' => 'Artillery', 'slug' => 'artillery', 'sector_id' => $combatOps->id, 'service' => 'army', 'description' => 'Artillery gunnery and fire support operations.']);
        $req($artillery, $wasce, 'any');
        $req($artillery, $degree, 'any', null, null, 'second_class_lower');
        $req($artillery, $masters, 'any');

        $mp = Corp::create(['name' => 'Military Police', 'slug' => 'military-police', 'sector_id' => $combatOps->id, 'service' => 'army', 'description' => 'Military law enforcement, discipline and security operations.']);
        $req($mp, $wasce, 'any');
        $req($mp, $degree, 'any', null, null, 'second_class_lower');

        // 2. Engineer Corps
        $engCorps = Corp::create(['name' => 'Engineer Corps', 'slug' => 'engineer-corps', 'sector_id' => $engineering->id, 'service' => 'army', 'description' => 'Military engineering including construction, demolitions, bridging and field defences.']);
        $req($engCorps, $wasce, 'any');
        $req($engCorps, $diploma, 'engineering');
        $req($engCorps, $degree, 'engineering', null, null, 'second_class_lower');
        $req($engCorps, $masters, 'engineering');
        $req($engCorps, $doctorate, 'engineering');

        // 3. EME
        $eme = Corp::create(['name' => 'Electrical & Mechanical Engineers (EME)', 'slug' => 'eme', 'sector_id' => $engineering->id, 'service' => 'army', 'description' => 'Maintenance and repair of military vehicles, weapons systems and electrical equipment.']);
        $req($eme, $wasce, 'any');
        $req($eme, $diploma, 'engineering');
        $req($eme, $degree, 'engineering', null, null, 'second_class_lower');
        $req($eme, $masters, 'engineering');

        // 4. Signal Corps
        $signal = Corp::create(['name' => 'Signal Corps', 'slug' => 'signal-corps', 'sector_id' => $itComms->id, 'service' => 'army', 'description' => 'Communication systems, signals intelligence and information warfare.']);
        $req($signal, $wasce, 'any');
        $req($signal, $diploma, 'stem');
        $req($signal, $degree, 'specific', ['Computer Science', 'Telecom Engineering', 'Computer Engineering', 'Electrical Engineering', 'Electronic Engineering', 'Instrumentation Engineering', 'Physics', 'ICT', 'Software Engineering', 'Aerospace Engineering', 'Material Sciences', 'Systems Engineering']);
        $req($signal, $masters, 'specific', ['Computer Science', 'Telecom Engineering', 'Computer Engineering', 'Electrical Engineering', 'Electronic Engineering', 'Cyber Security', 'Data Science']);

        // 5. IT Corps
        $it = Corp::create(['name' => 'Information Technology Corps', 'slug' => 'it-corps', 'sector_id' => $itComms->id, 'service' => 'army', 'description' => 'IT infrastructure, software development, database management and cyber defence.']);
        $req($it, $wasce, 'any');
        $req($it, $diploma, 'stem');
        $req($it, $degree, 'specific', ['Computer Science', 'Information Technology', 'Computer Engineering', 'Software Engineering', 'Cyber Security', 'Data Science', 'Telecom Engineering', 'ICT']);

        // 6. Supply & Transport
        $supply = Corp::create(['name' => 'Supply & Transport Corps', 'slug' => 'supply-transport', 'sector_id' => $logistics->id, 'service' => 'army', 'description' => 'Logistics, supply chain management, transport and quartermaster services.']);
        $req($supply, $wasce, 'any');
        $req($supply, $diploma, 'business');
        $req($supply, $degree, 'specific', ['Logistics', 'Supply Chain Management', 'Purchasing and Supply', 'Transport Management', 'Ports and Shipping Administration', 'Procurement', 'Business Administration', 'Food Science and Technology', 'Food Science and Nutrition', 'Fire and Disaster Management', 'Accounting', 'Finance']);
        $req($supply, $masters, 'business');

        // 7. Ordnance Corps
        $ordnance = Corp::create(['name' => 'Ordnance Corps', 'slug' => 'ordnance-corps', 'sector_id' => $logistics->id, 'service' => 'army', 'description' => 'Ammunition, explosives and weapons storage, maintenance and disposal (EOD).']);
        $req($ordnance, $wasce, 'any');
        $req($ordnance, $diploma, 'stem');
        $req($ordnance, $degree, 'specific', ['Chemistry', 'Physics', 'Biochemistry', 'Chemical Engineering', 'Materials Science', 'Mathematics']);

        // 8. Pay Corps
        $pay = Corp::create(['name' => 'Pay Corps', 'slug' => 'pay-corps', 'sector_id' => $adminFinance->id, 'service' => 'army', 'description' => 'Military payroll, accounting, budgeting and financial management.']);
        $req($pay, $wasce, 'any');
        $req($pay, $diploma, 'business');
        $req($pay, $degree, 'specific', ['Accounting', 'Finance', 'Banking', 'Business Administration', 'Economics', 'Actuarial Science']);

        // 9. Education Corps
        $eduCorps = Corp::create(['name' => 'Education Corps', 'slug' => 'education-corps', 'sector_id' => $educationSector->id, 'service' => 'army', 'description' => 'Military education, training and academic instruction for personnel.']);
        $req($eduCorps, $wasce, 'any');
        $req($eduCorps, $diploma, 'education');
        $req($eduCorps, $degree, 'specific', ['Education', 'Arts Education', 'Science Education', 'Mathematics Education', 'English', 'French', 'Arabic', 'Chinese', 'German', 'Geography', 'History', 'Physical Education']);
        $req($eduCorps, $masters, 'education');

        // 10. Clerk General Duties
        $clerk = Corp::create(['name' => 'Clerk General Duties', 'slug' => 'clerk-gd', 'sector_id' => $adminFinance->id, 'service' => 'army', 'description' => 'Office administration, secretarial duties and records management.']);
        $req($clerk, $wasce, 'any');
        $req($clerk, $diploma, 'business');

        // 11. Public Relations
        $pr = Corp::create(['name' => 'Public Relations Corps', 'slug' => 'public-relations', 'sector_id' => $mediaPR->id, 'service' => 'army', 'description' => 'Media relations, public affairs, journalism and communications.']);
        $req($pr, $wasce, 'any');
        $req($pr, $diploma, 'arts');
        $req($pr, $degree, 'specific', ['Journalism', 'Communication Studies', 'Media Studies', 'Public Relations', 'Graphic Design', 'Film Production', 'English', 'Marketing']);

        // 12. Physical Training / Sports
        $pt = Corp::create(['name' => 'Physical Training & Sports', 'slug' => 'physical-training', 'sector_id' => $educationSector->id, 'service' => 'army', 'description' => 'Physical fitness training, sports instruction and athletic representation.']);
        $req($pt, $wasce, 'any');
        $req($pt, $diploma, 'education');
        $req($pt, $degree, 'specific', ['Physical Education', 'Sports Science', 'Health and Physical Education']);

        // 13. Records Corps
        $records = Corp::create(['name' => 'Records Corps', 'slug' => 'records-corps', 'sector_id' => $adminFinance->id, 'service' => 'army', 'description' => 'Personnel records, documentation and data management.']);
        $req($records, $wasce, 'any');
        $req($records, $diploma, 'business');
        $req($records, $degree, 'any', null, null, 'second_class_lower');

        // 14. Catering
        $catering = Corp::create(['name' => 'Catering Corps', 'slug' => 'catering-corps', 'sector_id' => $logistics->id, 'service' => 'army', 'description' => 'Food preparation, mess management and hospitality services.']);
        $req($catering, $wasce, 'any');
        $req($catering, $diploma, 'specific', ['Food Science and Technology', 'Food Science and Nutrition', 'Hospitality Management', 'Catering']);
        $req($catering, $degree, 'specific', ['Food Science and Technology', 'Food Science and Nutrition', 'Hospitality Management']);

        // 15. Agricultural Extension
        $agri = Corp::create(['name' => 'Agricultural Extension', 'slug' => 'agricultural-extension', 'sector_id' => $agriculture->id, 'service' => 'army', 'description' => 'Agricultural services, farming operations and food production.']);
        $req($agri, $wasce, 'any');
        $req($agri, $diploma, 'stem');
        $req($agri, $degree, 'specific', ['Agriculture', 'Agricultural Engineering', 'Crop Science', 'Animal Science', 'Agribusiness', 'Forestry', 'Food Science']);
        $req($agri, $degree, 'agriculture');

        // 16. Band
        $band = Corp::create(['name' => 'Army Band', 'slug' => 'army-band', 'sector_id' => $musicBand->id, 'service' => 'army', 'description' => 'Military music, ceremonial performances and musical instruction.']);
        $req($band, $wasce, 'any');
        $req($band, $diploma, 'arts');
        $req($band, $degree, 'specific', ['Music', 'Music Education', 'Performing Arts']);

        // ─── NAVY CORPS ───

        // 17. Navy Executive Branch
        $navyExec = Corp::create(['name' => 'Navy Executive Branch', 'slug' => 'navy-executive', 'sector_id' => $maritime->id, 'service' => 'navy', 'description' => 'Navigation, seamanship, naval operations and maritime warfare.']);
        $req($navyExec, $wasce, 'any');
        $req($navyExec, $diploma, 'stem');
        $req($navyExec, $degree, 'specific', ['Marine and Nautical Science', 'Navigation', 'Meteorology', 'Physics', 'Mathematics', 'Geography', 'Oceanography', 'Marine Engineering', 'Mechanical Engineering', 'Electrical Engineering']);
        $req($navyExec, $degree, 'nautical');

        // 18. Navy Engineering
        $navyEng = Corp::create(['name' => 'Navy Engineering Branch', 'slug' => 'navy-engineering', 'sector_id' => $maritime->id, 'service' => 'navy', 'description' => 'Marine engineering, ship maintenance, propulsion systems and electrical systems.']);
        $req($navyEng, $wasce, 'any');
        $req($navyEng, $diploma, 'engineering');
        $req($navyEng, $degree, 'specific', ['Marine Engineering', 'Mechanical Engineering', 'Electrical Engineering', 'Electronic Engineering', 'Naval Architecture', 'Aeronautical Engineering']);
        $req($navyEng, $degree, 'nautical');

        // 19. Navy Supply & Secretarial
        $navySupply = Corp::create(['name' => 'Navy Supply & Secretarial', 'slug' => 'navy-supply-secretarial', 'sector_id' => $adminFinance->id, 'service' => 'navy', 'description' => 'Supply chain, accounting, secretarial duties and personnel administration.']);
        $req($navySupply, $wasce, 'any');
        $req($navySupply, $diploma, 'business');
        $req($navySupply, $degree, 'specific', ['Accounting', 'Business Administration', 'Logistics', 'Supply Chain Management', 'Purchasing and Supply', 'Secretarial Studies']);

        // 20. Navy Technical Branch
        $navyTech = Corp::create(['name' => 'Navy Technical Branch', 'slug' => 'navy-technical', 'sector_id' => $maritime->id, 'service' => 'navy', 'description' => 'Technical roles in communications, electronics, weapon systems and hull maintenance.']);
        $req($navyTech, $wasce, 'any');
        $req($navyTech, $diploma, 'engineering');
        $req($navyTech, $degree, 'specific', ['Electrical Engineering', 'Electronic Engineering', 'Mechanical Engineering', 'Telecom Engineering', 'Computer Engineering', 'Computer Science']);
        $req($navyTech, $degree, 'nautical');

        // ─── AIR FORCE CORPS ───

        // 21. Air Force Operations
        $afOps = Corp::create(['name' => 'Air Force Operations', 'slug' => 'af-operations', 'sector_id' => $aviation->id, 'service' => 'air_force', 'description' => 'Pilot training, air operations, air traffic control and flight safety.']);
        $req($afOps, $degree, 'specific', ['Physics', 'Mathematics', 'Aerospace Studies', 'Aviation Technology', 'Mechanical Engineering', 'Aeronautical Engineering', 'Electrical Engineering', 'Meteorology', 'Geography', 'Statistics', 'Engineering']);
        $req($afOps, $masters, 'specific', ['Aerospace Engineering', 'Aviation', 'Physics', 'Mathematics']);

        // 22. Air Force Engineering
        $afEng = Corp::create(['name' => 'Air Force Engineering', 'slug' => 'af-engineering', 'sector_id' => $aviation->id, 'service' => 'air_force', 'description' => 'Aircraft maintenance, aerospace engineering, avionics and ground support equipment.']);
        $req($afEng, $diploma, 'engineering');
        $req($afEng, $degree, 'specific', ['Aerospace Engineering', 'Mechanical Engineering', 'Electrical Engineering', 'Electronic Engineering', 'Aeronautical Engineering', 'Avionics', 'Aviation Technology', 'Computer Engineering', 'Material Sciences']);
        $req($afEng, $masters, 'engineering');

        // 23. Air Force Supply & Admin
        $afSupply = Corp::create(['name' => 'Air Force Supply & Administration', 'slug' => 'af-supply-admin', 'sector_id' => $adminFinance->id, 'service' => 'air_force', 'description' => 'Supply chain, logistics, administration and personnel management.']);
        $req($afSupply, $wasce, 'any');
        $req($afSupply, $diploma, 'business');
        $req($afSupply, $degree, 'specific', ['Business Administration', 'Accounting', 'Logistics', 'Supply Chain Management', 'Human Resource Management', 'Public Administration']);

        // 24. Air Force Ordnance
        $afOrd = Corp::create(['name' => 'Air Force Ordnance', 'slug' => 'af-ordnance', 'sector_id' => $logistics->id, 'service' => 'air_force', 'description' => 'Ammunition, explosives and weapons management for air force operations.']);
        $req($afOrd, $wasce, 'any');
        $req($afOrd, $diploma, 'stem');
        $req($afOrd, $degree, 'specific', ['Chemistry', 'Physics', 'Chemical Engineering', 'Materials Science']);

        // 25. Air Force Band
        $afBand = Corp::create(['name' => 'Air Force Band', 'slug' => 'af-band', 'sector_id' => $musicBand->id, 'service' => 'air_force', 'description' => 'Air force musical band for ceremonies and events.']);
        $req($afBand, $wasce, 'any');
        $req($afBand, $diploma, 'arts');
        $req($afBand, $degree, 'specific', ['Music', 'Music Education']);

        // ─── MEDICAL CORPS (Army/Navy/Air Force) ───
        $med = Corp::create(['name' => 'Medical Corps', 'slug' => 'medical-corps', 'sector_id' => $medical->id, 'service' => 'army', 'description' => 'Medical officers providing healthcare services to military personnel.']);
        $req($med, $degree, 'specific', ['Medicine', 'Surgery'], null, 'second_class_lower');
        $req($med, $masters, 'medical');
        $req($med, $doctorate, 'medical');

        $nursing = Corp::create(['name' => 'Nursing Corps', 'slug' => 'nursing-corps', 'sector_id' => $medical->id, 'service' => 'army', 'description' => 'Professional nursing care in military hospitals and field medical units.']);
        $req($nursing, $diploma, 'specific', ['Nursing', 'Midwifery', 'Public Health Nursing', 'Psychiatry Nursing']);
        $req($nursing, $degree, 'specific', ['Nursing', 'Midwifery', 'Public Health Nursing']);

        $pharmacy = Corp::create(['name' => 'Pharmacy Corps', 'slug' => 'pharmacy-corps', 'sector_id' => $medical->id, 'service' => 'army', 'description' => 'Pharmaceutical services, drug management and medical supply.']);
        $req($pharmacy, $diploma, 'specific', ['Pharmacy']);
        $req($pharmacy, $degree, 'specific', ['Pharmacy']);

        $alliedHealth = Corp::create(['name' => 'Allied Health Corps', 'slug' => 'allied-health', 'sector_id' => $medical->id, 'service' => 'army', 'description' => 'Laboratory science, radiography, physiotherapy, dietetics and other allied health professions.']);
        $req($alliedHealth, $diploma, 'specific', ['Laboratory Science', 'Radiography', 'Physiotherapy', 'Dietetics', 'Nutrition', 'Biomedical Science', 'Health Education', 'Medical Laboratory', 'Dental Technology', 'Dental Surgery', 'Optometry', 'Audiology', 'Speech Therapy', 'Biostatistics', 'Health Information', 'Prosthetics and Orthotics', 'Environmental Health', 'Disease Control', 'Veterinary', 'Biomedical Engineering']);
        $req($alliedHealth, $degree, 'medical');

        // ─── RELIGIOUS AFFAIRS ───
        $chaplain = Corp::create(['name' => 'Chaplain (Christian)', 'slug' => 'chaplain', 'sector_id' => $religious->id, 'service' => 'army', 'description' => 'Christian pastoral care, counselling and spiritual leadership.']);
        $req($chaplain, $degree, 'specific', ['Theology', 'Divinity', 'Religious Studies', 'Ministry', 'Pastoral Counselling'], null, 'second_class_lower');

        $imam = Corp::create(['name' => 'Imam (Islamic Affairs)', 'slug' => 'imam', 'sector_id' => $religious->id, 'service' => 'army', 'description' => 'Islamic pastoral care, counselling and spiritual leadership.']);
        $req($imam, $degree, 'specific', ['Islamic Studies', 'Arabic', 'Sharia Law', 'Theology'], null, 'second_class_lower');

        // ─── TECHNICAL TRADES (Army/Navy) ───
        $weldFabrication = Corp::create(['name' => 'Welding & Fabrication', 'slug' => 'welding-fabrication', 'sector_id' => $trades->id, 'service' => 'army', 'description' => 'Metal fabrication, welding and structural manufacturing.']);
        $req($weldFabrication, $wasce, 'any');
        $req($weldFabrication, $diploma, 'engineering');

        $carpentry = Corp::create(['name' => 'Carpentry & Joinery', 'slug' => 'carpentry-joinery', 'sector_id' => $trades->id, 'service' => 'army', 'description' => 'Woodworking, construction carpentry and furniture making.']);
        $req($carpentry, $wasce, 'any');

        $tailoring = Corp::create(['name' => 'Tailoring', 'slug' => 'tailoring', 'sector_id' => $trades->id, 'service' => 'army', 'description' => 'Military uniform and textile manufacturing and repairs.']);
        $req($tailoring, $wasce, 'any');
        $req($tailoring, $diploma, 'arts');

        $shipwright = Corp::create(['name' => 'Shipwright', 'slug' => 'shipwright', 'sector_id' => $trades->id, 'service' => 'navy', 'description' => 'Ship and boat construction, maintenance and repair.']);
        $req($shipwright, $wasce, 'any');
        $req($shipwright, $diploma, 'engineering');

        $marineEngine = Corp::create(['name' => 'Marine Engine Mechanic', 'slug' => 'marine-engine-mechanic', 'sector_id' => $trades->id, 'service' => 'navy', 'description' => 'Marine engine maintenance, repair and overhaul.']);
        $req($marineEngine, $wasce, 'any');
        $req($marineEngine, $diploma, 'engineering');

        $radioTech = Corp::create(['name' => 'Radio Technician', 'slug' => 'radio-technician', 'sector_id' => $trades->id, 'service' => 'navy', 'description' => 'Radio and communication equipment maintenance and repair.']);
        $req($radioTech, $wasce, 'any');
        $req($radioTech, $diploma, 'stem');

        // ─── NAVY ONLY trades ───
        $cookNavy = Corp::create(['name' => 'Navy Cook', 'slug' => 'navy-cook', 'sector_id' => $logistics->id, 'service' => 'navy', 'description' => 'Food preparation and mess management on naval vessels.']);
        $req($cookNavy, $wasce, 'any');
        $req($cookNavy, $diploma, 'specific', ['Catering', 'Food Science', 'Hospitality']);

        // ─── AIR FORCE ONLY roles ───
        $afCatering = Corp::create(['name' => 'Air Force Catering', 'slug' => 'af-catering', 'sector_id' => $logistics->id, 'service' => 'air_force', 'description' => 'Food services and mess management for air force personnel.']);
        $req($afCatering, $wasce, 'any');
        $req($afCatering, $diploma, 'specific', ['Catering', 'Food Science', 'Hospitality']);

        $afPT = Corp::create(['name' => 'Air Force Physical Training', 'slug' => 'af-pt', 'sector_id' => $educationSector->id, 'service' => 'air_force', 'description' => 'Fitness training and physical conditioning for air force personnel.']);
        $req($afPT, $wasce, 'any');
        $req($afPT, $diploma, 'education');
        $req($afPT, $degree, 'specific', ['Physical Education', 'Sports Science']);

        // ─── SHORT SERVICE COMMISSION (Masters level) ───
        $sscSignal = Corp::create(['name' => 'SSC Signal/Communications', 'slug' => 'ssc-signal', 'sector_id' => $itComms->id, 'service' => 'army', 'description' => 'Short Service Commission for communications specialists with professional certifications.']);
        $req($sscSignal, $masters, 'specific', ['Computer Science', 'Telecom Engineering', 'Computer Engineering', 'Cyber Security', 'Data Science', 'Electrical Engineering', 'Electronic Engineering'],
            ['CCNA', 'MCSE', 'MCSA', 'CompTIA N+', 'Cyber Security', 'Ethical Hacking', 'Fibre Optics']);

        $sscEngineer = Corp::create(['name' => 'SSC Engineering', 'slug' => 'ssc-engineering', 'sector_id' => $engineering->id, 'service' => 'army', 'description' => 'Short Service Commission for professional engineers with advanced qualifications.']);
        $req($sscEngineer, $masters, 'engineering', null, null, 'second_class_lower');

        // ─── SPORTSMEN (separate category) ───
        $sportsmen = Corp::create(['name' => 'Sportsmen (Army)', 'slug' => 'sportsmen-army', 'sector_id' => $educationSector->id, 'service' => 'army', 'description' => 'Athletes representing GAF in national and international competitions.']);
        $req($sportsmen, $wasce, 'any');

        // ─── NAVY EXECUTIVE (WASSCE) ───
        $navyWriter = Corp::create(['name' => 'Navy Writer (General Duties)', 'slug' => 'navy-writer', 'sector_id' => $adminFinance->id, 'service' => 'navy', 'description' => 'Clerical and administrative duties in the navy.']);
        $req($navyWriter, $wasce, 'any');

        $navyAccount = Corp::create(['name' => 'Navy Accountant', 'slug' => 'navy-accountant', 'sector_id' => $adminFinance->id, 'service' => 'navy', 'description' => 'Financial management and accounting for naval operations.']);
        $req($navyAccount, $wasce, 'any');
        $req($navyAccount, $diploma, 'business');
        $req($navyAccount, $degree, 'business', null, null, 'second_class_lower');

        // ─── LEGAL OFFICER CORPS ───
        $legalOfficer = Corp::create(['name' => 'Legal Officer Corps', 'slug' => 'legal-officer', 'sector_id' => $legal->id, 'service' => 'army', 'description' => 'Legal advisory, prosecution, and judicial services for the Ghana Armed Forces.']);
        $req($legalOfficer, $degree, 'specific', ['Law', 'LLB', 'Bachelor of Laws'], null, 'second_class_lower');
        $req($legalOfficer, $masters, 'legal');

        // ─── NAVY & AIR FORCE RELIGIOUS AFFAIRS ───
        $navyChaplain = Corp::create(['name' => 'Navy Chaplain (Christian)', 'slug' => 'navy-chaplain', 'sector_id' => $religious->id, 'service' => 'navy', 'description' => 'Christian pastoral care and spiritual leadership for naval personnel.']);
        $req($navyChaplain, $degree, 'specific', ['Theology', 'Divinity', 'Religious Studies', 'Ministry', 'Pastoral Counselling'], null, 'second_class_lower');

        $afChaplain = Corp::create(['name' => 'Air Force Chaplain (Christian)', 'slug' => 'af-chaplain', 'sector_id' => $religious->id, 'service' => 'air_force', 'description' => 'Christian pastoral care and spiritual leadership for air force personnel.']);
        $req($afChaplain, $degree, 'specific', ['Theology', 'Divinity', 'Religious Studies', 'Ministry', 'Pastoral Counselling'], null, 'second_class_lower');

        $navyImam = Corp::create(['name' => 'Navy Imam (Islamic Affairs)', 'slug' => 'navy-imam', 'sector_id' => $religious->id, 'service' => 'navy', 'description' => 'Islamic pastoral care and spiritual leadership for naval personnel.']);
        $req($navyImam, $degree, 'specific', ['Islamic Studies', 'Arabic', 'Sharia Law', 'Theology'], null, 'second_class_lower');

        $afImam = Corp::create(['name' => 'Air Force Imam (Islamic Affairs)', 'slug' => 'af-imam', 'sector_id' => $religious->id, 'service' => 'air_force', 'description' => 'Islamic pastoral care and spiritual leadership for air force personnel.']);
        $req($afImam, $degree, 'specific', ['Islamic Studies', 'Arabic', 'Sharia Law', 'Theology'], null, 'second_class_lower');

        // ─── ADDITIONAL SSC CORPS ───
        $sscMedical = Corp::create(['name' => 'SSC Medical Corps', 'slug' => 'ssc-medical', 'sector_id' => $medical->id, 'service' => 'army', 'description' => 'Short Service Commission for medical specialists with postgraduate qualifications.']);
        $req($sscMedical, $masters, 'specific', ['Medicine', 'Surgery', 'Public Health', 'Epidemiology', 'Internal Medicine']);

        $sscLegal = Corp::create(['name' => 'SSC Legal Officer', 'slug' => 'ssc-legal', 'sector_id' => $legal->id, 'service' => 'army', 'description' => 'Short Service Commission for legal professionals with advanced law degrees.']);
        $req($sscLegal, $masters, 'specific', ['Law', 'LLM', 'Master of Laws', 'International Law', 'Corporate Law']);

        $sscSupply = Corp::create(['name' => 'SSC Supply & Transport', 'slug' => 'ssc-supply-transport', 'sector_id' => $logistics->id, 'service' => 'army', 'description' => 'Short Service Commission for logistics and supply chain professionals.']);
        $req($sscSupply, $masters, 'specific', ['Logistics', 'Supply Chain Management', 'Procurement', 'Transport Management', 'Business Administration']);

        $sscEducation = Corp::create(['name' => 'SSC Education Corps', 'slug' => 'ssc-education', 'sector_id' => $educationSector->id, 'service' => 'army', 'description' => 'Short Service Commission for education specialists with postgraduate teaching qualifications.']);
        $req($sscEducation, $masters, 'specific', ['Education', 'Arts Education', 'Science Education', 'Mathematics Education', 'Educational Administration', 'Curriculum Studies']);

        $sscPay = Corp::create(['name' => 'SSC Pay Corps', 'slug' => 'ssc-pay', 'sector_id' => $adminFinance->id, 'service' => 'army', 'description' => 'Short Service Commission for finance and accounting professionals.']);
        $req($sscPay, $masters, 'specific', ['Accounting', 'Finance', 'Banking', 'Economics', 'Actuarial Science']);

        $sscPR = Corp::create(['name' => 'SSC Public Relations', 'slug' => 'ssc-public-relations', 'sector_id' => $mediaPR->id, 'service' => 'army', 'description' => 'Short Service Commission for communications and media professionals.']);
        $req($sscPR, $masters, 'specific', ['Journalism', 'Communication Studies', 'Media Studies', 'Public Relations', 'Marketing']);

        $sscCatering = Corp::create(['name' => 'SSC Catering Corps', 'slug' => 'ssc-catering', 'sector_id' => $logistics->id, 'service' => 'army', 'description' => 'Short Service Commission for hospitality and catering management professionals.']);
        $req($sscCatering, $masters, 'specific', ['Food Science and Technology', 'Food Science and Nutrition', 'Hospitality Management', 'Catering']);
    }
}
