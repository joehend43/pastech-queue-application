Add-Type -AssemblyName System.Windows.Forms

$url = "http://localhost:8000/display?left=1&right=2"
$chrome = "C:\Program Files\Google\Chrome\Application\chrome.exe"

$screen = [System.Windows.Forms.Screen]::AllScreens |
    Where-Object { -not $_.Primary } |
    Select-Object -First 1

if ($screen) {

    $x = $screen.Bounds.X
    $y = $screen.Bounds.Y

    $profilePath = "$env:TEMP\queue-display"

    Start-Process $chrome -ArgumentList @(
        "--new-window",
        "--incognito",
        "--user-data-dir=$profilePath",
        "--window-position=$x,$y",
        "--start-fullscreen",
        "--disable-gpu",
        "--autoplay-policy=no-user-gesture-required",
        $url
    )
}