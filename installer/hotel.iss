[Setup]
AppName=Hotel
AppVersion=1.0
AppPublisher=Hotel
DefaultDirName={localappdata}\Hotel
DisableProgramGroupPage=yes
PrivilegesRequired=lowest
OutputDir=Output
OutputBaseFilename=Hotel-Setup
SetupIconFile=hotel.ico
UninstallDisplayIcon={app}\hotel.ico
Compression=lzma2
SolidCompression=yes
WizardStyle=modern
ArchitecturesInstallIn64BitMode=x64compatible

[Languages]
Name: "spanish"; MessagesFile: "compiler:Languages\Spanish.isl"

[Files]
Source: "..\build\php\*"; DestDir: "{app}\php"; Flags: recursesubdirs ignoreversion
Source: "..\build\mariadb\*"; DestDir: "{app}\mariadb"; Flags: recursesubdirs ignoreversion
Source: "..\Hotel\*"; DestDir: "{app}\Hotel"; Flags: recursesubdirs ignoreversion
Source: "iniciar.ps1"; DestDir: "{app}"; Flags: ignoreversion
Source: "detener.ps1"; DestDir: "{app}"; Flags: ignoreversion
Source: "Hotel.vbs"; DestDir: "{app}"; Flags: ignoreversion
Source: "Cerrar.vbs"; DestDir: "{app}"; Flags: ignoreversion
Source: "hotel.ico"; DestDir: "{app}"; Flags: ignoreversion

[Icons]
Name: "{autodesktop}\Hotel"; Filename: "{sys}\wscript.exe"; Parameters: """{app}\Hotel.vbs"""; WorkingDir: "{app}"; IconFilename: "{app}\hotel.ico"
Name: "{autodesktop}\Cerrar Hotel"; Filename: "{sys}\wscript.exe"; Parameters: """{app}\Cerrar.vbs"""; WorkingDir: "{app}"; IconFilename: "{app}\hotel.ico"
Name: "{autoprograms}\Hotel\Hotel"; Filename: "{sys}\wscript.exe"; Parameters: """{app}\Hotel.vbs"""; WorkingDir: "{app}"; IconFilename: "{app}\hotel.ico"
Name: "{autoprograms}\Hotel\Cerrar Hotel"; Filename: "{sys}\wscript.exe"; Parameters: """{app}\Cerrar.vbs"""; WorkingDir: "{app}"; IconFilename: "{app}\hotel.ico"

[Run]
Filename: "{sys}\wscript.exe"; Parameters: """{app}\Hotel.vbs"""; Description: "Abrir Hotel ahora"; Flags: postinstall nowait skipifsilent

[UninstallRun]
Filename: "{sys}\wscript.exe"; Parameters: """{app}\Cerrar.vbs"""; RunOnceId: "CerrarHotel"; Flags: runhidden

[Code]
function PrepareToInstall(var NeedsRestart: Boolean): String;
var R: Integer;
begin
  if FileExists(ExpandConstant('{app}\detener.ps1')) then
    Exec(ExpandConstant('{sys}\WindowsPowerShell\v1.0\powershell.exe'),
      '-NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "' + ExpandConstant('{app}\detener.ps1') + '"',
      '', SW_HIDE, ewWaitUntilTerminated, R);
  Result := '';
end;
