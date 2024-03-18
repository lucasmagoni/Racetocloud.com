# Define the source folder and the final folder
$sourceFolder = ".\"
$finalFolder = ".\Final"
# Ensure the final folder exists
if (-not (Test-Path -Path $finalFolder)) {
    New-Item -ItemType Directory -Path $finalFolder
}

# Find the .exe file in the source folder, excluding IntuneWinAppUtil.exe
$exeFile = Get-ChildItem -Path $sourceFolder -Filter "*.exe" | Where-Object { $_.Name -ne "IntuneWinAppUtil.exe" } | Select-Object -First 1

if ($null -eq $exeFile) {
    Write-Error "No executable file found in the source folder."
    exit
}

# The path to the IntuneWinAppUtil.exe tool
$intuneWinAppUtilPath = ".\IntuneWinAppUtil.exe"

# Generate the .intunewin file
& $intuneWinAppUtilPath -c $sourceFolder -s $exeFile.Name -o $finalFolder

if ($?) {
    Write-Host "Package creation successful."

    # Assuming there's only one .intunewin file, rename it to match the .exe file's name
    $intunewinFile = Get-ChildItem -Path $finalFolder -Filter "*.intunewin" | Select-Object -First 1
    if ($null -ne $intunewinFile) {
        # Construct the new file name with the .intunewin extension
        $newFileName = $exeFile.BaseName + ".intunewin"
        $newFilePath = Join-Path -Path $finalFolder -ChildPath $newFileName

        # Perform the rename operation
        Rename-Item -Path $intunewinFile.FullName -NewName $newFileName

        Write-Host "Package renamed to match the .exe file."
    }
    else {
        Write-Host "No .intunewin file found to rename."
    }
}
else {
    Write-Error "Package creation failed."
}