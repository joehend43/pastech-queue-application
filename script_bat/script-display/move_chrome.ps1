Add-Type -AssemblyName System.Windows.Forms

Add-Type @'
using System;
using System.Runtime.InteropServices;

public class Win32 {
    [DllImport("user32.dll")]
    public static extern bool MoveWindow(
        IntPtr hWnd,
        int X,
        int Y,
        int nWidth,
        int nHeight,
        bool bRepaint
    );

    [DllImport("user32.dll")]
    public static extern bool ShowWindow(
        IntPtr hWnd,
        int nCmdShow
    );

    [DllImport("user32.dll")]
    public static extern bool SetForegroundWindow(
        IntPtr hWnd
    );
}
'@

# Tunggu Chrome siap
$chrome = $null

for ($i = 0; $i -lt 20; $i++) {

    $chrome = Get-Process chrome -ErrorAction SilentlyContinue |
        Where-Object {
            $_.MainWindowHandle -ne 0 -and
            $_.MainWindowTitle -ne ""
        } |
        Select-Object -First 1

    if ($chrome) {
        break
    }

    Start-Sleep -Milliseconds 500
}

if (-not $chrome) {
    exit
}

# Cari monitor kedua
$target = [System.Windows.Forms.Screen]::AllScreens |
    Where-Object { -not $_.Primary } |
    Select-Object -First 1

if ($target) {

    # $b = $target.Bounds
    $b = $target.WorkingArea

    # pastikan window aktif
    [Win32]::ShowWindow(
        $chrome.MainWindowHandle,
        3
    )

    [Win32]::SetForegroundWindow(
        $chrome.MainWindowHandle
    )

    Start-Sleep -Milliseconds 500

    # lempar ke monitor kedua
    [Win32]::MoveWindow(
        $chrome.MainWindowHandle,
        $b.X,
        $b.Y,
        $b.Width,
        $b.Height,
        $true
    )

    Start-Sleep -Milliseconds 500

    # fullscreen
    [System.Windows.Forms.SendKeys]::SendWait('{F11}')
}