from app.db.neo4j import Neo4jConnection
import logging

logger = logging.getLogger(__name__)

async def log_fraud_to_graph(reporter_id: str, target_user_id: str, reason: str, description: str):
    """
    Logs relationship and node data to Neo4j to build a fraud graph.
    """
    driver = await Neo4jConnection.get_driver()
    
    query = """
    MERGE (reporter:User {uid: $reporter_id})
    MERGE (target:User {uid: $target_user_id})
    CREATE (reporter)-[:REPORTED_FRAUD {
        reason: $reason, 
        description: $description, 
        timestamp: datetime()
    }]->(target)
    """
    
    try:
        async with driver.session() as session:
            await session.run(
                query,
                reporter_id=reporter_id,
                target_user_id=target_user_id,
                reason=reason,
                description=description
            )
        logger.info(f"Successfully logged fraud report to graph for target {target_user_id}")
    except Exception as e:
        logger.error(f"Failed to log fraud to neo4j graph: {e}")
