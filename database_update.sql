-- Script de actualización de la estructura de la tabla competencias

-- Primero, desactivar temporalmente la verificación de claves foráneas
SET FOREIGN_KEY_CHECKS=0;

-- Verificar si existen datos en evaluations que usan competencias
SELECT COUNT(*) INTO @eval_count FROM evaluations WHERE competencia_id IS NOT NULL;

-- Si no hay datos relacionados, proceder con la reconstrucción
DROP TABLE IF EXISTS competencias;

-- Crear la tabla competencias con la estructura correcta
CREATE TABLE competencias (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL COMMENT 'ID del curso académico',
  `bimestre` varchar(2) NOT NULL COMMENT 'Número de bimestre (1-4)',
  `teacher_id` int(11) NOT NULL COMMENT 'ID del docente',
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre de la competencia',
  `porcentaje` int(3) NOT NULL COMMENT 'Porcentaje de la calificación (1-100)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_course_bimestre_teacher` (`course_id`, `bimestre`, `teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Añadir columnas a la tabla evaluations para las competencias si no existen
ALTER TABLE `evaluations` 
ADD COLUMN IF NOT EXISTS `bimestre` varchar(2) NULL COMMENT 'Número de bimestre (1-4)' AFTER `seccion`,
ADD COLUMN IF NOT EXISTS `competencia_id` int(11) NULL COMMENT 'ID de la competencia relacionada' AFTER `bimestre`;

-- Crear índices para mejorar el rendimiento
ALTER TABLE `evaluations`
ADD INDEX IF NOT EXISTS `idx_competencia_bimestre` (`competencia_id`, `bimestre`);

-- Reactivar la verificación de claves foráneas
SET FOREIGN_KEY_CHECKS=1;
