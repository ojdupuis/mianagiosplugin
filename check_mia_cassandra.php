#!/usr/bin/php -q
<?php
   require_once('lib/MiaNagiosPluginIndexed.inc.php');

   class MiaNagiosPlugin_CheckCassandraStats extends MiaNagiosPluginIndexed{
      
	protected $types = array(	'org.apache.cassandra.request%3Atype%3DReadStage'=>Array('ReadStage',Array('ActiveCount','CompletedTasks','PendingTasks'),null,Array('','c',''),'<',7,'>',1),
			'org.apache.cassandra.request%3Atype%3DMutationStage'=>Array('MutationStage',Array('ActiveCount','CompletedTasks','PendingTasks'),null,Array('','c',''),'<',7,'>',1),
			'org.apache.cassandra.internal%3Atype%3DGossipStage'=>Array('GossipStage',Array('ActiveCount','CompletedTasks','PendingTasks'),null,Array('','c',''),'<',7,'>',1),
			'org.apache.cassandra.db%3Atype%3DCompactionManager'=>Array('Compaction',Array('CompletedTasks','PendingTasks'),null,Array('c',''),'<',7,'>',1),
			'org.apache.cassandra.db%3Atype%3DStorageProxy'=>Array('Latency',Array('RecentRangeLatencyMicros','RecentReadLatencyMicros','RecentWriteLatencyMicros'),Array('Range','Read','Write'),Array('','',''),'<',7,'>',1),
			'java.lang%3Atype%3DGarbageCollector%2Cname%3DConcurrentMarkSweep'=>Array('internals',Array('CollectionCount'),Array('GarbageCollections'),Array('c'),'<',7,'>',1),
			'java.lang%3Atype%3DMemory'=>Array('internals',Array('HeapMemoryUsage','HeapMemoryUsage'),Array('MaxJavaHeap','MaxJavaNoHeap'),Array('B','B'),'max=',1,', used=',0),
			'java.lang%3Atype%3DMemory'=>Array('internals',Array('HeapMemoryUsage','HeapMemoryUsage'),Array('JavaHeapUsed','JavaNoHeapUsed'),Array('B','B'),', used=',1,'}',0),
		);
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setSpecialProperties()
       */
   	
      protected function setSpecialProperties(){
         $this->setSpecialProperty('intituleStatus','CASSANDRA');
         $this->setSpecialProperty('titre_aide','Plugins Nagios monitoring CASSANDRA');
         $this->setSpecialProperty('commentaire_aide','');
      }     

  
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setInputs()
       */      
       public function setInputs(){
         trigger_error("start",E_USER_NOTICE);
         $this->addInput('host','/(\-H)\s+([^\s]+)/',false);         
         trigger_error("end",E_USER_NOTICE);
      }
      
      /**
       * (non-PHPdoc)
       * @see nagios/plugins/lib/MiaNagiosPlugin#setIndicators()
       */
      protected function setIndicators(){
	$this->addIndicatorIndexed('cass');
	$this->setIndicatorUnit('cass',0);
	foreach($this->types as $objtype=>$dumm)
	{
		list($type,$vars,$names,$uoms,$sep1,$field1,$sep2,$field2) = $dumm;
		foreach($vars as $k=>$var)
		{
			if(!$names || !isset($names[$k]))
				$name = $var;
			else	$name = $names[$k];
			$this->addIndicatorIndexed("cass.$type.$name");
			$this->setIndicatorUnit("cass.$type.$name",0);
			$this->setIndicatorUnit("cass.$type.$name",$uoms[$k]);
		}
	}
      }
      
      protected function _preliminarySetUp(){
         // Get CASSANDRA stats
         
         trigger_error("start",E_USER_NOTICE);
	$tab=array();
        $host = $this->getInput('host');

	#Number of connections - Numero de connexions
	if ( $host == "localhost" )
	  $connections=trim(`netstat -tn|grep ESTABLISHED|awk '{print $4}'|grep 9160|wc -l`);
	else 
	  $connections=trim(`ssh $host netstat -tn|grep ESTABLISHED|awk '{print $4}'|grep 9160|wc -l`);

	$tab['cass']['connections']=$connections;

	#Tasks in ReadStage - Tasques en ReadStage
	foreach($this->types as $objtype=>$dumm)
	{
		list($type,$vars,$names,$uoms,$sep1,$field1,$sep2,$field2) = $dumm;
		$res = $this->filterItems(
			file("http://$host:8081/mbean?objectname=$objtype"),
			'/'.implode("|",$vars).'/',
			$sep1,$field1,$sep2,$field2
			);
		foreach($vars as $k=>$var)
		{
			if(!$names || !isset($names[$k]))
				$name = $var;
			else	$name = $names[$k];
			$uom = $uoms[$k];
			$data .= "$type.$name=".$res[$k]."$uom;null;null;null,null ";
			$tab["cass.$type.$name"][$type.$name]=$res[$k];
		}
	}
         trigger_error("end",E_USER_NOTICE);
         return $tab;
      }

	public function filterItems($item,$match,$sep1,$field1,$sep2,$field2)
	{
		$res = array();
		foreach($item as $k=>$v)
			if(preg_match($match,$v))
			{
				$dum1 = explode($sep1,$v);
				$dum1 = $dum1[$field1];
				$dum2 = explode($sep2,$dum1);
				$dum2 = $dum2[$field2];
				$res[] = $dum2;
			}
		return $res;
	}

   }
   
   $check=new MiaNagiosPlugin_CheckCassandraStats();
   $check->OutputResult();
