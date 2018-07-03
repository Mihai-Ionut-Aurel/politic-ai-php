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
from nltk.stem import SnowballStemmer

def fill(array , subjects):
	result = []
	for subject in subjects:
		if not subject in array:
			result.append(0)
	result.extend(list(array.values()))
	return result

#Stem the word, add word to stemdict with stemmedword as key and original word as part of list of values. Eventually look for all words in stemdict when printing keywords.	
def stem(word, stemdict, stemmer):
	stemmedword = stemmer.stem(word)
	if not stemmedword in stemdict:
		stemdict[stemmedword] = []
	if not word in stemdict[stemmedword]:
		stemdict[stemmedword].append(word)
	return stemmedword

stemmer = SnowballStemmer('dutch')	
stemdict = {}	
statements = []
with open('plenairemeetings.csv',newline='\n', encoding="ascii", errors="surrogateescape") as csvfile:
	reader = csv.reader(csvfile, delimiter=',', quotechar='"')
	i=0
	#print([x for x in reader])
	for row in reader:
		statement = {}
		statement['id'] = row[0]
		statement['type'] = row[1]
		statement['minute_id'] = row[2]
		statement['subject_id'] = row[3]
		statement['person_id'] = row[4]
		statement['text'] = row[5]
		statements.append(statement)

#Dict (word => Dict(Subject => Frequency))
wordlist = {}
for statement in statements:
	if not  statement['subject_id'] in wordlist:
		wordlist[statement['subject_id']] = ''
	wordlist[statement['subject_id']] += statement['text']
	
#Implement SnowballStemmer --> Add used words to frequencylist, where word used as key is stemmed word, while a list is added where the transfered words are still stored. 
#Create a function for stemming.


frequencylist = {}
for key, value in wordlist.items():
	totalsize = len(value.split())
	stopwordless = ' '.join([word for word in value.split() if word not in stopwords.words('dutch')])
	punctuation = '\\' + '|\\'.join(string.punctuation)
	stopwordless = re.sub(punctuation, '', stopwordless)
	stopwordless = stopwordless.lower()
	for word in Counter([stem(word, stemdict, stemmer) for word in stopwordless.split()]).most_common():
		if not word[0] in frequencylist:
			frequencylist[word[0]] = {}
		frequencylist[word[0]][key] = word[1]/totalsize
		

subjects = list(set(x['subject_id'] for x in statements))

keywords = {}
for word, frequencies in frequencylist.items():
	for subject, frequency in frequencies.items():
		prob = scipy.stats.norm(numpy.mean(fill(frequencies, subjects)), numpy.std(fill(frequencies, subjects))).cdf(frequency)
		if math.isnan(prob):
			print(fill(frequencies,subjects))
		if prob > 0.95:
			if subject not in keywords:
				keywords[subject] = []
			keywords[subject].append(word)
		

wordpersonlist = {}
for statement in statements:
	if not statement['person_id'] in wordpersonlist:
		wordpersonlist[statement['person_id']] = {}
	if not statement['subject_id'] in wordpersonlist[statement['person_id']]:
		wordpersonlist[statement['person_id']][statement['subject_id']] = ''
	wordpersonlist[statement['person_id']][statement['subject_id']] += statement['text']
	
frequencylist = {}
for person, wordlist in wordpersonlist.items():
	for key, value in wordlist.items():
		totalsize = len(value.split())
		stopwordless = ' '.join([word for word in value.split() if word not in stopwords.words('dutch')])
		punctuation = '\\' + '|\\'.join(string.punctuation)
		stopwordless = re.sub(punctuation, '', stopwordless)
		stopwordless = stopwordless.lower()
		for word in Counter([stem(word, stemdict, stemmer) for word in stopwordless.split()]).most_common():
			if not person in frequencylist:
				frequencylist[person] = {}
			if not word[0] in frequencylist[person]:
				frequencylist[person][word[0]] = {}
			frequencylist[person][word[0]][key] = word[1]/totalsize
		


keywordsperson = {}
for person, wordlist in frequencylist.items():
	for word, frequencies in wordlist.items():
		for subject, frequency in frequencies.items():
			#returns NAN
			prob = scipy.stats.norm(numpy.mean(fill(frequencies, subjects)), numpy.std(fill(frequencies, subjects))).cdf(frequency)
			if prob > 0.95:
				if person not in keywordsperson:
					keywordsperson[person] = {}
				if subject not in keywordsperson[person]:
					keywordsperson[person][subject] = []
				keywordsperson[person][subject].append(word)

with open('keywordstotal.txt', 'w', newline='\n', encoding="ascii", errors="surrogateescape") as keywordtotalfile:
	for subject, wordlist in keywords.items():
		for word in wordlist:
			for realword in stemdict[word]:
				keywordtotalfile.write('"' + subject + '";"' + realword + '"\n')

with open('keywordsperson.txt', 'w', newline='\n', encoding="ascii", errors="surrogateescape") as keywordpersonfile:
	for person, subjectlist in keywordsperson.items():
		for subject, wordlist in subjectlist.items():
			for word in wordlist:
				for realword in stemdict[word]:
					keywordpersonfile.write('"' + person  + '";"' + subject + '";"' + realword + '"\n')