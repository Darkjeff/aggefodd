# Procedure ajout nouvelle table

1) **Créer fichier llx_le_nom_de_la_table.sql**,  
   Il contient la création de la table sans les index
2) **Créer fichier llx_le_nom_de_la_table.key.sql**,  
   Il contient la création des index et contraintes
3) Si besoin créer le fichier data_le_nom_de_la_table.sql,  
   Il contient les valeurs à insérer en base
4) **Créer le fichier de mise à jour**,  
   Le format est update_x.y.z-a.b.c.sql  
   Il doit à partir de la version 6.18.0 de Agefodd contenir l'ensemble des points 1 à 3
   (oui, c'est redondant, mais c'est normal)


### Notes :
- Les points 1 à 3 ne sont (à partir de la version 6.18.0) exécutés que lors de la première installation.
