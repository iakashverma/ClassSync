<?php

require_once __DIR__ . '/db.php';

function academic_courses(): array
{
    return ['BCA', 'MCA', 'BTech', 'MTech'];
}

function academic_years(): array
{
    return ['1st Year', '2nd Year', '3rd Year', '4th Year'];
}

function academic_sections(): array
{
    return ['A', 'B', 'C'];
}

function academic_subjects(): array
{
    return ['Java', 'DSA', 'PHP', 'Networking', 'Linux', 'DBMS'];
}

function academic_subject_limit_per_class(): int
{
    return 5;
}

function academic_table_columns(PDO $db, string $table): array
{
    $allowed = ['courses', 'years', 'sections', 'subjects'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Unsupported table: ' . $table);
    }

    $stmt = $db->query('SHOW COLUMNS FROM ' . $table);
    $columns = [];
    foreach ($stmt->fetchAll() as $row) {
        $columns[] = (string) $row['Field'];
    }

    return $columns;
}

function academic_has_column(PDO $db, string $table, string $column): bool
{
    return in_array($column, academic_table_columns($db, $table), true);
}

function academic_subject_column(PDO $db): string
{
    if (academic_has_column($db, 'subjects', 'subject_name')) {
        return 'subject_name';
    }

    if (academic_has_column($db, 'subjects', 'name')) {
        return 'name';
    }

    throw new RuntimeException('Subjects table has no subject label column.');
}

function academic_normalize_year(string $value): string
{
    $plain = strtolower(trim($value));
    $plain = str_replace([' ', 'year'], '', $plain);

    if ($plain === '1st') {
        return '1st Year';
    }
    if ($plain === '2nd') {
        return '2nd Year';
    }
    if ($plain === '3rd') {
        return '3rd Year';
    }
    if ($plain === '4th') {
        return '4th Year';
    }

    return trim($value);
}

function ensure_predefined_academic_data(PDO $db): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $subjectCols = academic_table_columns($db, 'subjects');
    $hasLegacyName = in_array('name', $subjectCols, true);
    $hasSubjectName = in_array('subject_name', $subjectCols, true);

    if (!$hasSubjectName && $hasLegacyName) {
        $db->exec('ALTER TABLE subjects ADD COLUMN subject_name VARCHAR(120) NULL AFTER id');
        $db->exec("UPDATE subjects SET subject_name = name WHERE subject_name IS NULL OR subject_name = ''");
        $subjectCols = $db->query('SHOW COLUMNS FROM subjects')->fetchAll();
        $hasSubjectName = true;
        $hasLegacyName = false;
        foreach ($subjectCols as $col) {
            if (($col['Field'] ?? '') === 'name') {
                $hasLegacyName = true;
                break;
            }
        }
    }

    $insertCourse = $db->prepare('INSERT INTO courses (name) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM courses WHERE name = ?)');
    foreach (academic_courses() as $courseName) {
        $insertCourse->execute([$courseName, $courseName]);
    }

    $courseRows = $db->query('SELECT id, name FROM courses ORDER BY id')->fetchAll();
    $courseMap = [];
    foreach ($courseRows as $row) {
        $name = (string) $row['name'];
        if (!isset($courseMap[$name])) {
            $courseMap[$name] = (int) $row['id'];
        }
    }
    $defaultCourseId = $courseMap['BCA'] ?? ((int) ($courseRows[0]['id'] ?? 1));

    $yearHasCourseId = academic_has_column($db, 'years', 'course_id');
    if ($yearHasCourseId) {
        $insertYear = $db->prepare('INSERT INTO years (course_id, year_name) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM years WHERE year_name = ?)');
        foreach (academic_years() as $yearName) {
            $insertYear->execute([$defaultCourseId, $yearName, $yearName]);
        }
    } else {
        $insertYear = $db->prepare('INSERT INTO years (year_name) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM years WHERE year_name = ?)');
        foreach (academic_years() as $yearName) {
            $insertYear->execute([$yearName, $yearName]);
        }
    }

    $yearRows = $db->query('SELECT id, year_name FROM years ORDER BY id')->fetchAll();
    $yearMap = [];
    foreach ($yearRows as $row) {
        $normalized = academic_normalize_year((string) $row['year_name']);
        if (!isset($yearMap[$normalized])) {
            $yearMap[$normalized] = (int) $row['id'];
        }
    }
    $defaultYearId = $yearMap['1st Year'] ?? ((int) ($yearRows[0]['id'] ?? 1));

    $sectionHasYearId = academic_has_column($db, 'sections', 'year_id');
    if ($sectionHasYearId) {
        $insertSection = $db->prepare('INSERT INTO sections (year_id, section_name) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM sections WHERE section_name = ?)');
        foreach (academic_sections() as $sectionName) {
            $insertSection->execute([$defaultYearId, $sectionName, $sectionName]);
        }
    } else {
        $insertSection = $db->prepare('INSERT INTO sections (section_name) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM sections WHERE section_name = ?)');
        foreach (academic_sections() as $sectionName) {
            $insertSection->execute([$sectionName, $sectionName]);
        }
    }

    $sectionRows = $db->query('SELECT id, section_name FROM sections ORDER BY id')->fetchAll();
    $sectionMap = [];
    foreach ($sectionRows as $row) {
        $normalized = strtoupper(trim((string) $row['section_name']));
        if (!isset($sectionMap[$normalized])) {
            $sectionMap[$normalized] = (int) $row['id'];
        }
    }
    $defaultSectionId = $sectionMap['A'] ?? ((int) ($sectionRows[0]['id'] ?? 1));

    $subjectCols = academic_table_columns($db, 'subjects');
    $hasSubjectName = in_array('subject_name', $subjectCols, true);
    $hasLegacyName = in_array('name', $subjectCols, true);
    $subjectHasCourseId = in_array('course_id', $subjectCols, true);
    $subjectHasYearId = in_array('year_id', $subjectCols, true);
    $subjectHasSectionId = in_array('section_id', $subjectCols, true);

    if ($hasLegacyName && $hasSubjectName) {
        $db->exec("UPDATE subjects SET subject_name = name WHERE (subject_name IS NULL OR subject_name = '')");
    }

    $insertColumns = [];
    $insertPlaceholders = [];

    if ($hasLegacyName) {
        $insertColumns[] = 'name';
        $insertPlaceholders[] = '?';
    }

    if ($hasSubjectName) {
        $insertColumns[] = 'subject_name';
        $insertPlaceholders[] = '?';
    }

    if ($subjectHasCourseId) {
        $insertColumns[] = 'course_id';
        $insertPlaceholders[] = '?';
    }

    if ($subjectHasYearId) {
        $insertColumns[] = 'year_id';
        $insertPlaceholders[] = '?';
    }

    if ($subjectHasSectionId) {
        $insertColumns[] = 'section_id';
        $insertPlaceholders[] = '?';
    }

    if (empty($insertColumns)) {
        throw new RuntimeException('Subjects table cannot be seeded because required columns are missing.');
    }

    $insertSubjectSql = 'INSERT INTO subjects (' . implode(', ', $insertColumns) . ') SELECT ' . implode(', ', $insertPlaceholders) . ' WHERE NOT EXISTS (SELECT 1 FROM subjects WHERE subject_name = ?)';
    $insertSubject = $db->prepare($insertSubjectSql);

    foreach (academic_subjects() as $subjectName) {
        $params = [];

        if ($hasLegacyName) {
            $params[] = $subjectName;
        }

        if ($hasSubjectName) {
            $params[] = $subjectName;
        }

        if ($subjectHasCourseId) {
            $params[] = $defaultCourseId;
        }

        if ($subjectHasYearId) {
            $params[] = $defaultYearId;
        }

        if ($subjectHasSectionId) {
            $params[] = $defaultSectionId;
        }

        $params[] = $subjectName;
        $insertSubject->execute($params);
    }

    if ($hasLegacyName && $hasSubjectName) {
        $db->exec("UPDATE subjects SET name = subject_name WHERE (name IS NULL OR name = '') AND subject_name IS NOT NULL");
    }

    $done = true;
}

function get_predefined_courses(PDO $db): array
{
    ensure_predefined_academic_data($db);

    $rows = $db->query('SELECT id, name FROM courses ORDER BY id')->fetchAll();
    $map = [];
    foreach ($rows as $row) {
        $key = strtolower(trim((string) $row['name']));
        if (!isset($map[$key])) {
            $map[$key] = ['id' => (int) $row['id'], 'name' => (string) $row['name']];
        }
    }

    $result = [];
    foreach (academic_courses() as $name) {
        $key = strtolower($name);
        if (isset($map[$key])) {
            $result[] = $map[$key];
        }
    }

    return $result;
}

function get_predefined_years(PDO $db): array
{
    ensure_predefined_academic_data($db);

    $rows = $db->query('SELECT id, year_name FROM years ORDER BY id')->fetchAll();
    $map = [];
    foreach ($rows as $row) {
        $key = academic_normalize_year((string) $row['year_name']);
        if (!isset($map[$key])) {
            $map[$key] = ['id' => (int) $row['id'], 'year_name' => $key];
        }
    }

    $result = [];
    foreach (academic_years() as $yearName) {
        if (isset($map[$yearName])) {
            $result[] = $map[$yearName];
        }
    }

    return $result;
}

function get_predefined_sections(PDO $db): array
{
    ensure_predefined_academic_data($db);

    $rows = $db->query('SELECT id, section_name FROM sections ORDER BY id')->fetchAll();
    $map = [];
    foreach ($rows as $row) {
        $key = strtoupper(trim((string) $row['section_name']));
        if (!isset($map[$key])) {
            $map[$key] = ['id' => (int) $row['id'], 'section_name' => $key];
        }
    }

    $result = [];
    foreach (academic_sections() as $sectionName) {
        if (isset($map[$sectionName])) {
            $result[] = $map[$sectionName];
        }
    }

    return $result;
}

function get_predefined_subjects(PDO $db): array
{
    ensure_predefined_academic_data($db);

    $subjectColumn = academic_subject_column($db);
    $rows = $db->query('SELECT id, ' . $subjectColumn . ' AS subject_name FROM subjects ORDER BY id')->fetchAll();

    $map = [];
    foreach ($rows as $row) {
        $name = trim((string) $row['subject_name']);
        if ($name === '') {
            continue;
        }

        $key = strtolower($name);
        if (!isset($map[$key])) {
            $map[$key] = ['id' => (int) $row['id'], 'subject_name' => $name];
        }
    }

    $result = [];
    foreach (academic_subjects() as $subjectName) {
        $key = strtolower($subjectName);
        if (isset($map[$key])) {
            $result[] = $map[$key];
        }
    }

    return $result;
}

function academic_ids_from_rows(array $rows): array
{
    $ids = [];
    foreach ($rows as $row) {
        $ids[(int) $row['id']] = true;
    }
    return $ids;
}

function academic_valid_class_selection(PDO $db, int $courseId, int $yearId, int $sectionId, ?int $subjectId = null): bool
{
    $courseIds = academic_ids_from_rows(get_predefined_courses($db));
    $yearIds = academic_ids_from_rows(get_predefined_years($db));
    $sectionIds = academic_ids_from_rows(get_predefined_sections($db));

    if (!isset($courseIds[$courseId]) || !isset($yearIds[$yearId]) || !isset($sectionIds[$sectionId])) {
        return false;
    }

    if ($subjectId === null) {
        return true;
    }

    $subjectIds = academic_ids_from_rows(get_predefined_subjects($db));
    return isset($subjectIds[$subjectId]);
}

function academic_can_assign_subject_to_class(PDO $db, int $courseId, int $yearId, int $sectionId, int $subjectId, ?int $excludeAssignmentId = null): array
{
    $dupSql = 'SELECT id FROM teacher_assignments WHERE course_id = ? AND year_id = ? AND section_id = ? AND subject_id = ?';
    $dupParams = [$courseId, $yearId, $sectionId, $subjectId];

    if ($excludeAssignmentId !== null) {
        $dupSql .= ' AND id <> ?';
        $dupParams[] = $excludeAssignmentId;
    }

    $dupSql .= ' LIMIT 1';
    $dupStmt = $db->prepare($dupSql);
    $dupStmt->execute($dupParams);

    if ($dupStmt->fetch()) {
        return [false, 'Selected subject is already assigned for this class.'];
    }

    $countSql = 'SELECT COUNT(DISTINCT subject_id) FROM teacher_assignments WHERE course_id = ? AND year_id = ? AND section_id = ?';
    $countParams = [$courseId, $yearId, $sectionId];

    if ($excludeAssignmentId !== null) {
        $countSql .= ' AND id <> ?';
        $countParams[] = $excludeAssignmentId;
    }

    $countStmt = $db->prepare($countSql);
    $countStmt->execute($countParams);
    $subjectCount = (int) $countStmt->fetchColumn();

    if ($subjectCount >= academic_subject_limit_per_class()) {
        return [false, 'Only 5 subjects can be assigned for one class.'];
    }

    return [true, 'ok'];
}
