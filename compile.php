<?php
// Quick utility: compile tailwind + show file sizes
$project = '/var/www/html/tnp@iiitmanipur';
chdir($project);
$output = shell_exec("{$project}/tailwindcss -i frontend/assets/css/tailwind.input.css -o frontend/assets/css/app.css --minify 2>&1");
echo "Tailwind output:\n" . $output . "\n";
$size = file_exists("{$project}/frontend/assets/css/app.css") ? filesize("{$project}/frontend/assets/css/app.css") : 0;
echo "app.css size: " . number_format($size) . " bytes\n";
echo "Done at: " . date('H:i:s') . "\n";
