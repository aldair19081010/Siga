# SQL Issues Fixed in the Grade Report System

## Issue 1: Incorrect Table Reference
The system was trying to use a non-existent table called `evaluation_competencias` when it should have been using `evaluation_grades`.

**Before:**
```sql
SELECT AVG(ec.grade) as promedio_final
FROM evaluation_competencias ec
INNER JOIN evaluations e ON ec.evaluation_id = e.id AND e.school_id = ec.school_id
INNER JOIN academic_courses ac ON e.course_id = ac.id AND ac.school_id = e.school_id
WHERE ec.student_id = ? AND ec.school_id = ?
```

**After:**
```sql
SELECT AVG(eg.grade) as promedio_final
FROM evaluation_grades eg
INNER JOIN evaluations e ON eg.evaluation_id = e.id
INNER JOIN teacher_courses tc ON e.teacher_course_id = tc.id
INNER JOIN academic_courses ac ON tc.course_id = ac.id AND ac.school_id = ?
WHERE eg.student_id = ?
```

## Issue 2: Incorrect Column References
The system was using incorrect column references (`e.course_id`, `e.grado`, `e.seccion`) that don't exist in the evaluations table.

**Before:**
```sql
WHERE tc.course_id = e.course_id 
AND tc.grado = e.grado 
AND tc.seccion = e.seccion
```

**After:**
```sql
WHERE tc2.id = e.teacher_course_id
```

## Issue 3: Main Report Query
Fixed the join logic in the main report query to use proper table relationships.

**Before:**
```sql
FROM evaluation_grades eg
INNER JOIN student s ON eg.student_id = s.id AND s.school_id = ?
INNER JOIN evaluations e ON eg.evaluation_id = e.id
INNER JOIN academic_courses ac ON e.course_id = ac.id AND ac.school_id = ?
INNER JOIN teacher_courses tc ON e.course_id = tc.course_id AND e.grado = tc.grado AND e.seccion = tc.seccion AND tc.school_id = ?
```

**After:**
```sql
FROM evaluation_grades eg
INNER JOIN student s ON eg.student_id = s.id AND s.school_id = ?
INNER JOIN evaluations e ON eg.evaluation_id = e.id
INNER JOIN teacher_courses tc ON e.teacher_course_id = tc.id
INNER JOIN academic_courses ac ON tc.course_id = ac.id AND ac.school_id = ?
```

## Issue 4: WHERE Clause Filter References
Updated the filter conditions to reference the correct tables and columns.

**Before:**
```sql
if (!empty($course_id_filter)) { $where_clauses[] = "e.course_id = ?"; $params[] = $course_id_filter; $types .= "i"; }
```

**After:**
```sql
if (!empty($course_id_filter)) { $where_clauses[] = "tc.course_id = ?"; $params[] = $course_id_filter; $types .= "i"; }
```

These changes align the SQL queries with the actual database schema, ensuring that the grade report system functions correctly.
