# -*- coding:utf-8 -*-
import base64
from xml.dom import minidom
import re

basePath = '../skins'
skinName = 'skin'
skinPath = basePath + '/' + skinName + '-min.xml'
imgPath = '..'

# skinPath
skinFile = open(skinPath,'r')
skin = minidom.parse(skinFile)

#这里一共获取了5个component，组成数组components
components = skin.getElementsByTagName('component')

'''在components中对每个component遍历'''
for component in components:

	'''获取每个在component的名字'''
	componentName = component.attributes['name']

	'''获取每个component中的所有element的组合elements'''
	elements = component.getElementsByTagName('element')

	'''在elements中对每个element遍历'''
	for element in elements:

		elementPath = imgPath + '/' + componentName.value + '/' + element.attributes['src'].value
		imageText = base64.b64encode(open(elementPath,'rb').read())
		element.attributes['src'].value = 'data:image/png;base64,' + imageText


skinText = skin.toxml()

outputPath = basePath + '/' + skinName + '.xml'
outputFile = open(outputPath,'w')
outputFile.write(skinText)
outputFile.close()
