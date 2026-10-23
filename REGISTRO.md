| Fase | Qué generó la IA | Qué corregí o rechacé | Por qué |
|------|------------------|-----------------------|---------|
| 0 · Preparación | — | XAMPP no cargaba phpMyAdmin; usé Laragon (PHP 8 + MySQL) | Falla del entorno, mismo stack exigido |
| 1 · D0 | D0.md con problema, usuarios, proceso actual, RF1–RF4, RNF1–RNF7, RN1–RN5, fuera de alcance y supuestos | Faltaba un requisito no funcional de manejo de errores; pedí agregar el RNF8 | Sin RNF8 no quedaba trazado que los errores se manejen con excepciones propias y nunca se muestren trazas al usuario |