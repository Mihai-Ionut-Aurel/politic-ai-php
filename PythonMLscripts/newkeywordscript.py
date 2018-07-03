import csv
import re
import scipy.stats
import numpy
import math
import string
#from stop-words import safe_get_stop_words
from nltk.corpus import stopwords
from nltk.stem import SnowballStemmer

from sklearn.linear_model import LogisticRegression
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn import metrics


csv.field_size_limit(1000000)


# How to connect the different keywords?
# Logistic Regression With following Features:
# Word size	???													--> 	Matchkeywords.py
# Percentage in subject keyword list  							-->		getpropabilityoccurence()
# Number of occurrences of keywords in other subject in dossier	--> 	PHP + Matchkeywords.py
# Presence in Title?												-->		PHP + Matchkeywords.py
# Location in text 												-->		Keywords.py + Matchkeywords.py
# Occurence of keyword in same category?							--> 	PHP + Matchkeywords.py
# Number of times available in person subject list

def fill(array,subjects):
    result = []
    for subject in subjects:
        if not subject in array:
            result.append(0)
    result.extend(list(array.values()))
    return result


def getallfeatures(word):
    features = []
    features[0] = len(word)
    return features


# Stem the word, add word to stemdict with stemmedword as key and original word as part of list of values. Eventually look for all words in stemdict when printing keywords.
def stem(word,stemdict,stemmer):
    stemmedword = stemmer.stem(word)
    if not stemmedword in stemdict:
        stemdict[stemmedword] = []
    if not word in stemdict[stemmedword]:
        stemdict[stemmedword].append(word)
    return stemmedword


dutchstemmer = SnowballStemmer('dutch')
stemdictionary = {}
subjectlist = {}
with open('newfulltext.txt',newline='\n',encoding="ascii",errors="surrogateescape") as csvfile:
    reader = csv.reader(csvfile,delimiter=',',quotechar='"')
    i = 0
    # print([x for x in reader])
    for row in reader:
        subject = {'id': row[0],'title': row[1],'dossier_id': row[2],'dossier_name': row[3],'category': row[4],
               'text': row[5]}
        subjectlist[row[0]]=subject
        i+=1


def openytrain():
    with open('wordlistset.txt',newline='\n', encoding='ascii', errors='surrogateescape') as classes:
        classifications = {}
        i = 0
        for line in classes:
            if ':' in line and line.split(':')[1] != '\r\n':
                if i > 2 and i < 5:
                    parts = line.split(':')
                    tuple = (re.sub('"','',parts[0].split(',')[1]),re.sub('"','',parts[0].split(',')[0]))
                    classifications[tuple] = parts[1].strip()
                i+=1
        return classifications


def removeCrapAndStem(word):
    if word not in stopwords.words('dutch'):
        punctuation = '\\' + '|\\'.join(string.punctuation)
        stopwordless = re.sub(punctuation,'',word)
        stopwordless = stopwordless.lower()
        return stem(stopwordless,stemdictionary,dutchstemmer)


def getwordlist(subjects):
    wordlist = {}
    for id, subject in subjects.items():
        wordlist[id] = [removeCrapAndStem(word) for word in subject['text'].split()]
    return wordlist


def printwordlist():
    wordlist = getwordlist(subjectlist)
    with open('wordlist.txt','w',encoding='ascii',errors='surrogateescape') as wordlistfile:
        for subject,wordlistsubject in wordlist.items():
            for word in wordlistsubject:
                wordlistfile.write('"' + subject + '","' + word + '":\n')


def getfrequencylist(subjects):
    frequencylist = {}
    for id, subject in subjects.items():
        frequencylist[id] = {}
        for word in subject['text'].split():
            stemmedword = removeCrapAndStem(word)
            if not stemmedword in frequencylist[id]:
                frequencylist[id][stemmedword] = 0
            frequencylist[id][stemmedword] += 1
    return frequencylist


def getprobabilityoccurrence(frequencylist, word, subjectid):
    subjectfrequency = frequencylist[subjectid][word]
    frequencytotal = [(wordlist[word] if word in wordlist else 0) for subject, wordlist in frequencylist.items()]
    return scipy.stats.norm(numpy.mean(frequencytotal), numpy.std(frequencytotal)).cdf(subjectfrequency)


def getprobabilityoccurrencedossier(frequencylist, word, dossierid):
    dossierprobability = {}
    for id, subject in subjectlist.items():
        if not subject['dossier_id'] in dossierprobability:
            dossierprobability[subject['dossier_id']] = []
        dossierprobability[subject['dossier_id']].append(frequencylist[id][word] if word in frequencylist[id] else 0)
    dossierfrequency = sum(dossierprobability[dossierid])/len(dossierprobability[dossierid])
    dossiertotal = [(sum(frequencies)/len(frequencies)) for dossier, frequencies in dossierprobability.items()]
    return scipy.stats.norm(numpy.mean(dossiertotal), numpy.std(dossiertotal)).cdf(dossierfrequency)

def getprobabilityoccurrencecategory( frequencylist, word, category):
    categoryprobability = {}
    for id,subject in subjectlist.items():
        for categorysubject in subject['category'].split(';'):
            if not categorysubject in categoryprobability:
                categoryprobability[categorysubject] = []
            categoryprobability[categorysubject].append(frequencylist[id][word] if word in frequencylist[id] else 0)
    for id, subject in subjectlist.items():
        for categorysubject in subject['category'].split(';'):
            if not categorysubject in categoryprobability:
                categoryprobability[categorysubject] = []
            categoryprobability[category].append(frequencylist[id][word] if word in frequencylist[id] else 0)
    categoryfrequency = sum(categoryprobability[category])/len(categoryprobability[category])
    categorytotal = [(sum(frequencies)/len(frequencies)) for categoryid, frequencies in categoryprobability.items()]
    return scipy.stats.norm(numpy.mean(categorytotal), numpy.std(categorytotal)).cdf(categoryfrequency)


def getfeatures():
    frequencylist = getfrequencylist(subjectlist)
    featureslist = {}
    for id,subject in subjectlist.items():
        for word in [removeCrapAndStem(word) for word in subject['text'].split()]:
            tuple = (word, id)
            if not tuple in featureslist:
                featureslist[tuple] = {}
            if word in stemdictionary:
                featureslist[tuple]['words'] = stemdictionary[word]
            else:
                featureslist[tuple]['words'] = word
            if getprobabilityoccurrence(frequencylist,word,id)==getprobabilityoccurrencedossier(frequencylist, word, subject['dossier_id']):
                print(tuple)
            featureslist[tuple]['subject'] = id
            featureslist[tuple]['subjectkeyword'] = getprobabilityoccurrence(frequencylist,word,id)
            featureslist[tuple]['dossierkeyword'] = getprobabilityoccurrencedossier(frequencylist, word, subject['dossier_id'])
            featureslist[tuple]['intitlesubject'] = 1 if word!= None and word in subject['title'] else 0
            featureslist[tuple]['categorykeyword'] = max([getprobabilityoccurrencecategory(frequencylist, word,category) for category in subject['category'].split(';')])
            featureslist[tuple]['dossiertimessubject'] = getprobabilityoccurrencedossier(frequencylist, word, subject['dossier_id']) * getprobabilityoccurrence(frequencylist,word,id)
    return featureslist

sample = []


def createtraining(featuredict):
    yvals = openytrain()
    train_x = numpy.zeros(shape=(len(yvals),5))
    train_y = numpy.zeros(shape=(len(yvals),1))
    i = 0
    for key, values in featuredict.items():
        if key in yvals and not math.isnan(values['subjectkeyword']) and not math.isnan(values['dossierkeyword']) \
                and not math.isnan(values['intitlesubject']) and not math.isnan(values['categorykeyword']) \
                and not math.isnan(values['dossiertimessubject']):
            sample.append(key)
            train_x[i] = [numpy.power(values['subjectkeyword'],2), numpy.power(values['dossierkeyword'],2),
                          values['intitlesubject'], values['categorykeyword'], values['dossiertimessubject']]
            train_y[i] = [yvals[key]]
            i+=1
    print(i)
    return (train_x, train_y)

content = []
stopwords = stopwords.words('dutch')
for subject,value in subjectlist.items():
    content.append(' '.join([word for word in value['text'].split() if word not in stopwords]))

tfidf = TfidfVectorizer()

n = 10
response =tfidf.fit_transform(content)
feature_array = numpy.array(tfidf.get_feature_names())
with open('newtfidfkeywords.txt', 'w' ,newline='\n',encoding="ascii",errors="surrogateescape" ) as outputfile:
    i = 0
    for index,subject in subjectlist.items():
        tfidf_sorting = numpy.argsort(response[i].toarray()).flatten()[::-1]
        top_n = feature_array[tfidf_sorting][:n]
        outputfile.write(index + ':' + ','.join(top_n) + '\n')
        i+=1



#print(tfidf.inverse_transform())



#logreg = LogisticRegression()
#training = createtraining(getfeatures())
#logreg.fit(training[0], training[1])
#yvals = openytrain()
#print(logreg.coef_)
#print(training[1])
#results = []
#for i in range (1, len(sample)):
    #if logreg.predict(numpy.array([training[0][i]]))[0]==1:
    #    results.append(sample[i])
    #print(sample[i])
#print(results)






# Implement SnowballStemmer --> Add used words to frequencylist, where word used as key
# is stemmed word, while a list is added where the transferred words are still stored.
# Create a function for stemming.
# getWordList(subjectlist)


# def getFrequenctWordsForSubject(text):
