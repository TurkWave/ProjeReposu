@echo off
set MYSQL="C:\xampp\mysql\bin\mysql.exe"
echo === issues (after functional test) ===
%MYSQL% -u root staj_app -e "SELECT id,internship_id,reported_by,category,anonymous,status,LEFT(description,30) AS d FROM issues ORDER BY id;"
echo === company_confirmations (internship 3) ===
%MYSQL% -u root staj_app -e "SELECT internship_id,confirmed,feedback,JSON_EXTRACT(evaluation,'$.q1') AS q1 FROM company_confirmations WHERE internship_id=3;"
echo === internships (internship 3) ===
%MYSQL% -u root staj_app -e "SELECT id,status,company_confirmed FROM internships WHERE id=3;"
echo === DONE ===