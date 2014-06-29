Remove-Item "build" -ErrorAction "SilentlyContinue" -recurse -force

$rutaProd = "build\production\BO\"
$scriptPath = split-path -parent $MyInvocation.MyCommand.Definition

#Ejecutamos sencha CMD
sencha app build production

#Copiamos los archivos y directorios necesarios
Copy-Item "archivos\" $rutaProd -recurse -force
Copy-Item "clases\" $rutaProd -recurse -force
#Copy-Item "images\" $rutaProd -recurse -force

#Copy-Item "images\" $rutaProd -recurse -force
Copy-Item "config.php" $rutaProd -force
Copy-Item "index.php" $rutaProd -force
#Copy-Item "style.css" $rutaProd -force

#Eliminamos archivos innecesarios
#Remove-Item "$rutaProd\archivos\documentos\*.*" -Force -Recurse -ErrorAction SilentlyContinue
#Remove-Item "$rutaProd\archivos\miniaturas\*.*" -Force -Recurse -ErrorAction SilentlyContinue
Remove-Item "$rutaProd\archivos\cache\*.*" -Force -Recurse -ErrorAction SilentlyContinue

#Ponemos index.php en modo produccion
$ie=New-Object -ComObject InternetExplorer.Application
$ie.Navigate("$($scriptPath)\$($rutaProd)index.html")
$ie.visible = $false
while ($ie.busy) {
	Start-Sleep -Milliseconds 100
}
$txt = @($ie.Document.GetElementsByTagName("script"))
$myScript = "<script type='text/javascript'>$($txt[0].innerHTML)</script>"

$new = Get-Content $rutaProd\index.php | Foreach-Object {$_ -replace "<script id=`"microloader`" type=`"text/javascript`" src=`"bootstrap.js`"></script>",  $myScript }
Set-Content $rutaProd\index.php $new
Remove-Item "$rutaProd\index.html" -ErrorAction "SilentlyContinue"
#Remove-Item "$rutaProd\cache.appcache" -ErrorAction "SilentlyContinue"


Write-Host " "
Write-Host "Proyecto comprimido en $rutaProd, pulse cualquier tecla para continuar."
$x = $host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")