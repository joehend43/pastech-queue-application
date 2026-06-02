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
}
'@

$chrome = Get-Process chrome |
Where-Object {$_.MainWindowHandle -ne 0} |
Select-Object -Last 1

if ($chrome) {

    $target =
    [System.Windows.Forms.Screen]::AllScreens |
    Where-Object { -not $_.Primary } |
    Select-Object -First 1

    if ($target) {

        $b = $target.Bounds

        [Win32]::ShowWindow(
            $chrome.MainWindowHandle,
            3
        )

        [Win32]::MoveWindow(
            $chrome.MainWindowHandle,
            $b.X,
            $b.Y,
            $b.Width,
            $b.Height,
            $true
        )
    }
}

Add-Type -AssemblyName System.Windows.Forms
[System.Windows.Forms.SendKeys]::SendWait('{F11}')