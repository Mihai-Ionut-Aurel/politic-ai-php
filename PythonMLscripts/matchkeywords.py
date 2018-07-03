import csv
import sys
import re
from collections import Counter
from bs4 import BeautifulSoup
import scipy.stats
import numpy
import json
import math
import string
from nltk.corpus import stopwords

keywordstotal = {}
with open('keywordstotal.csv',newline='\n', encoding="ascii", errors="surrogateescape") as csvfile:
	reader = csv.reader(csvfile, delimiter=';', quotechar='"')
	#print([x for x in reader])
	for row in reader:	
		if not row[0] in keywordstotal:
			keywordstotal[row[0]] = []
		keywordstotal[row[0]].append(row[1])

keywordsperson = {}
with open('keywordsperson.txt',newline='\n', encoding="ascii", errors="surrogateescape") as csvfile:
	reader = csv.reader(csvfile, delimiter=';', quotechar='"')
	#print([x for x in reader])
	for row in reader:
		keyword = {}
		if not row[1] in keywordsperson:
			keywordsperson[row[1]] = {}
		if not row[0] in keywordsperson[row[1]]:
			keywordsperson[row[1]][row[0]] = []
		keywordsperson[row[1]][row[0]].append(row[2]) 

#Get Subject + word and count persons
resultlist = {}
for subjecttotal, wordlisttotal in keywordstotal.items():
	resultlist[subjecttotal] = {}
	for subjectperson, personwordlist in keywordsperson.items():
		if subjecttotal == subjectperson:
			for word in wordlisttotal:
				if not word in resultlist[subjecttotal]:
					resultlist[subjecttotal][word] = 0
				resultlist[subjecttotal][word] += 1 
			for person, wordlistperson in personwordlist.items():
				for personword in wordlistperson:
					if personword in resultlist[subjecttotal]:
						resultlist[subjecttotal][personword] += 1 

with open('keywordsprocessed.csv', 'w', newline='\n', encoding='ascii', errors='surrogateescape') as outputfile:
	for subject, keywords in resultlist.items():
		for word, count in keywords.items():
			if count > 3:
				outputfile.write('"' + str(subject) + '","' + word + '","' + str(count) + '"\n')